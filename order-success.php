<?php
$pageTitle = "Order Successful";
include 'includes/header.php';
require_once 'config/database.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirectWithMessage("login.php", "Please login to view order details.", "error");
}

$orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if ($orderId <= 0) {
    redirectWithMessage("index.php", "Invalid order ID.", "error");
}

// Get order details
$stmt = $pdo->prepare("
    SELECT o.*, u.full_name, u.email 
    FROM orders o 
    JOIN users u ON o.user_id = u.user_id 
    WHERE o.order_id = ? AND o.user_id = ?
");
$stmt->execute([$orderId, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    redirectWithMessage("index.php", "Order not found.", "error");
}

// Get order items
$stmt = $pdo->prepare("
    SELECT oi.*, m.name, m.image 
    FROM order_items oi 
    JOIN medicines m ON oi.medicine_id = m.medicine_id 
    WHERE oi.order_id = ?
");
$stmt->execute([$orderId]);
$orderItems = $stmt->fetchAll();
?>

<div class="container">
    <div style="text-align: center; background: white; padding: 3rem; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); margin-bottom: 2rem;">
        <div style="font-size: 4rem; color: #28a745; margin-bottom: 1rem;">✅</div>
        <h1 style="color: #28a745; margin-bottom: 1rem;">Order Placed Successfully!</h1>
        <p style="font-size: 1.2rem; color: #666; margin-bottom: 2rem;">
            Thank you for your order. Your medicines will be delivered soon.
        </p>
        <div style="background: #f8f9fa; padding: 1rem; border-radius: 5px; display: inline-block;">
            <strong>Order ID: #<?php echo $orderId; ?></strong>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
        
        <!-- Order Details -->
        <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
            <h2 style="color: #2c5aa0; margin-bottom: 1.5rem;">Order Details</h2>
            
            <div style="margin-bottom: 1rem;">
                <strong>Order ID:</strong> #<?php echo $order['order_id']; ?>
            </div>
            
            <div style="margin-bottom: 1rem;">
                <strong>Order Date:</strong> <?php echo date('F j, Y \a\t g:i A', strtotime($order['order_date'])); ?>
            </div>
            
            <div style="margin-bottom: 1rem;">
                <strong>Status:</strong> 
                <span style="background: #17a2b8; color: white; padding: 0.25rem 0.75rem; border-radius: 15px; font-size: 0.9rem;">
                    <?php echo ucfirst(str_replace('_', ' ', $order['status'])); ?>
                </span>
            </div>
            
            <div style="margin-bottom: 1rem;">
                <strong>Payment Method:</strong> <?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?>
            </div>
            
            <div style="margin-bottom: 1rem;">
                <strong>Total Amount:</strong> 
                <span style="color: #28a745; font-weight: 700; font-size: 1.2rem;">
                    <?php echo formatPrice($order['total_amount']); ?>
                </span>
            </div>
        </div>
        
        <!-- Shipping Details -->
        <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
            <h2 style="color: #2c5aa0; margin-bottom: 1.5rem;">Shipping Information</h2>
            
            <div style="margin-bottom: 1rem;">
                <strong>Customer Name:</strong><br>
                <?php echo htmlspecialchars($order['full_name']); ?>
            </div>
            
            <div style="margin-bottom: 1rem;">
                <strong>Email:</strong><br>
                <?php echo htmlspecialchars($order['email']); ?>
            </div>
            
            <div style="margin-bottom: 1rem;">
                <strong>Shipping Address:</strong><br>
                <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?>
            </div>
            
            <div style="background: #d1ecf1; border: 1px solid #bee5eb; padding: 1rem; border-radius: 5px;">
                <h4 style="color: #0c5460; margin-bottom: 0.5rem;">📦 Delivery Information</h4>
                <p style="color: #0c5460; margin: 0; font-size: 0.9rem;">
                    Your order will be delivered within 2-3 business days. 
                    You will receive a tracking number via email once your order is shipped.
                </p>
            </div>
        </div>
    </div>

    <!-- Order Items -->
    <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); margin: 2rem 0;">
        <h2 style="color: #2c5aa0; margin-bottom: 1.5rem;">Order Items</h2>
        
        <?php foreach ($orderItems as $item): ?>
            <div style="display: flex; align-items: center; padding: 1rem 0; border-bottom: 1px solid #eee;">
                <div style="width: 80px; height: 80px; margin-right: 1rem; overflow: hidden; border-radius: 5px;">
                    <img src="<?php echo htmlspecialchars($item['image']); ?>" 
                         alt="<?php echo htmlspecialchars($item['name']); ?>"
                         style="width: 100%; height: 100%; object-fit: cover;"
                         onerror="this.src='https://placehold.co/80x80?text=Medicine+Product'">
                </div>
                
                <div style="flex: 1;">
                    <h4 style="color: #2c5aa0; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($item['name']); ?></h4>
                    <div style="color: #666;">
                        Quantity: <?php echo $item['quantity']; ?> × <?php echo formatPrice($item['price']); ?>
                    </div>
                </div>
                
                <div style="font-weight: 700; color: #28a745; font-size: 1.1rem;">
                    <?php echo formatPrice($item['price'] * $item['quantity']); ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Next Steps -->
    <div style="background: #f8f9fa; padding: 2rem; border-radius: 10px; text-align: center;">
        <h3 style="color: #2c5aa0; margin-bottom: 1.5rem;">What's Next?</h3>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
            <div style="background: white; padding: 1.5rem; border-radius: 10px;">
                <div style="font-size: 2rem; color: #17a2b8; margin-bottom: 0.5rem;">📧</div>
                <h4>Email Confirmation</h4>
                <p style="font-size: 0.9rem; color: #666;">You'll receive an order confirmation email shortly.</p>
            </div>
            
            <div style="background: white; padding: 1.5rem; border-radius: 10px;">
                <div style="font-size: 2rem; color: #fd7e14; margin-bottom: 0.5rem;">📋</div>
                <h4>Prescription Verification</h4>
                <p style="font-size: 0.9rem; color: #666;">Our pharmacist will verify any prescription requirements.</p>
            </div>
            
            <div style="background: white; padding: 1.5rem; border-radius: 10px;">
                <div style="font-size: 2rem; color: #28a745; margin-bottom: 0.5rem;">🚚</div>
                <h4>Fast Delivery</h4>
                <p style="font-size: 0.9rem; color: #666;">Your medicines will be delivered within 2-3 business days.</p>
            </div>
        </div>
        
        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            <a href="index.php" class="btn btn-primary">Continue Shopping</a>
            <a href="orders.php" class="btn" style="background: #6c757d; color: white;">View All Orders</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>