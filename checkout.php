<?php
$pageTitle = "Checkout";
include 'includes/header.php';
require_once 'config/database.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirectWithMessage("login.php", "Please login to checkout.", "error");
}

// Get cart items
$cartItems = getCartItems($_SESSION['user_id']);
$cartTotal = getCartTotal($_SESSION['user_id']);

// Redirect if cart is empty
if (empty($cartItems)) {
    redirectWithMessage("cart.php", "Your cart is empty.", "error");
}

// Get user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shippingAddress = sanitizeInput($_POST['shipping_address']);
    $paymentMethod = sanitizeInput($_POST['payment_method']);
    
    // Validation
    if (empty($shippingAddress)) {
        $errors[] = "Shipping address is required.";
    }
    
    if (empty($paymentMethod)) {
        $errors[] = "Payment method is required.";
    }
    
    // Check stock availability again
    foreach ($cartItems as $item) {
        $stmt = $pdo->prepare("SELECT stock_quantity FROM medicines WHERE medicine_id = ?");
        $stmt->execute([$item['medicine_id']]);
        $currentStock = $stmt->fetchColumn();
        
        if ($currentStock < $item['quantity']) {
            $errors[] = "Insufficient stock for " . $item['name'] . ". Available: " . $currentStock;
        }
    }
    
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            // Create order
            $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, shipping_address, payment_method) VALUES (?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $cartTotal, $shippingAddress, $paymentMethod]);
            $orderId = $pdo->lastInsertId();
            
            // Add order items and update stock
            foreach ($cartItems as $item) {
                // Add order item
                $stmt = $pdo->prepare("INSERT INTO order_items (order_id, medicine_id, quantity, price) VALUES (?, ?, ?, ?)");
                $stmt->execute([$orderId, $item['medicine_id'], $item['quantity'], $item['price']]);
                
                // Update stock
                $stmt = $pdo->prepare("UPDATE medicines SET stock_quantity = stock_quantity - ? WHERE medicine_id = ?");
                $stmt->execute([$item['quantity'], $item['medicine_id']]);
            }
            
            // Clear cart
            clearCart($_SESSION['user_id']);
            
            $pdo->commit();
            
            redirectWithMessage("order-success.php?order_id=" . $orderId, "Order placed successfully!");
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Failed to process order. Please try again.";
        }
    }
}
?>

<div class="container">
    <h1 style="color: #2c5aa0; margin-bottom: 2rem;">Checkout</h1>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; align-items: start;">
        
        <!-- Order Summary -->
        <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
            <h3 style="color: #2c5aa0; margin-bottom: 1.5rem;">Order Summary</h3>
            
            <?php foreach ($cartItems as $item): ?>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem 0; border-bottom: 1px solid #eee;">
                    <div>
                        <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                        <br>
                        <small style="color: #666;">Qty: <?php echo $item['quantity']; ?> × <?php echo formatPrice($item['price']); ?></small>
                    </div>
                    <div style="font-weight: 600; color: #28a745;">
                        <?php echo formatPrice($item['price'] * $item['quantity']); ?>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <div style="padding: 1rem 0; font-size: 1.2rem; font-weight: 700; color: #2c5aa0; border-top: 2px solid #2c5aa0; margin-top: 1rem;">
                Total: <?php echo formatPrice($cartTotal); ?>
            </div>
        </div>
        
        <!-- Checkout Form -->
        <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
            <h3 style="color: #2c5aa0; margin-bottom: 1.5rem;">Billing & Shipping</h3>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul style="margin: 0; padding-left: 1.5rem;">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="checkout.php">
                <div class="form-group">
                    <label for="customer_name">Customer Name:</label>
                    <input type="text" id="customer_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" 
                           readonly style="background: #f8f9fa;">
                </div>
                
                <div class="form-group">
                    <label for="customer_email">Email:</label>
                    <input type="email" id="customer_email" value="<?php echo htmlspecialchars($user['email']); ?>" 
                           readonly style="background: #f8f9fa;">
                </div>
                
                <div class="form-group">
                    <label for="customer_phone">Phone:</label>
                    <input type="tel" id="customer_phone" value="<?php echo htmlspecialchars($user['phone'] ?? 'Not provided'); ?>" 
                           readonly style="background: #f8f9fa;">
                </div>
                
                <div class="form-group">
                    <label for="shipping_address">Shipping Address: <span style="color: red;">*</span></label>
                    <textarea id="shipping_address" name="shipping_address" rows="4" required 
                              placeholder="Enter your complete shipping address"><?php echo isset($_POST['shipping_address']) ? htmlspecialchars($_POST['shipping_address']) : htmlspecialchars($user['address'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="payment_method">Payment Method: <span style="color: red;">*</span></label>
                    <select id="payment_method" name="payment_method" required>
                        <option value="">Select Payment Method</option>
                        <option value="cash_on_delivery" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] === 'cash_on_delivery') ? 'selected' : ''; ?>>Cash on Delivery</option>
                        <option value="credit_card" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] === 'credit_card') ? 'selected' : ''; ?>>Credit Card</option>
                        <option value="debit_card" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] === 'debit_card') ? 'selected' : ''; ?>>Debit Card</option>
                        <option value="online_banking" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] === 'online_banking') ? 'selected' : ''; ?>>Online Banking</option>
                    </select>
                </div>
                
                <div style="background: #f8f9fa; padding: 1rem; border-radius: 5px; margin: 1.5rem 0;">
                    <h4 style="color: #2c5aa0; margin-bottom: 0.5rem;">Order Notes:</h4>
                    <ul style="font-size: 0.9rem; color: #666; margin: 0; padding-left: 1.5rem;">
                        <li>Prescription medicines will require valid prescription upon delivery</li>
                        <li>Delivery charges may apply based on location</li>
                        <li>Expected delivery: 2-3 business days</li>
                        <li>For any queries, contact support at (555) 123-4567</li>
                    </ul>
                </div>
                
                <button type="submit" class="btn btn-success btn-full" style="font-size: 1.1rem; padding: 1rem;">
                    Place Order - <?php echo formatPrice($cartTotal); ?>
                </button>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>