<?php
$pageTitle = "Shopping Cart";
include 'includes/header.php';
require_once 'config/database.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirectWithMessage("login.php", "Please login to view your cart.", "error");
}

// Handle cart updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_quantity':
                $cartId = (int)$_POST['cart_id'];
                $newQuantity = (int)$_POST['quantity'];
                
                if ($newQuantity > 0) {
                    $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ? AND user_id = ?");
                    $stmt->execute([$newQuantity, $cartId, $_SESSION['user_id']]);
                    redirectWithMessage("cart.php", "Cart updated successfully!");
                } else {
                    // Remove item if quantity is 0 or less
                    $stmt = $pdo->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?");
                    $stmt->execute([$cartId, $_SESSION['user_id']]);
                    redirectWithMessage("cart.php", "Item removed from cart!");
                }
                break;
                
            case 'remove_item':
                $cartId = (int)$_POST['cart_id'];
                $stmt = $pdo->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?");
                $stmt->execute([$cartId, $_SESSION['user_id']]);
                redirectWithMessage("cart.php", "Item removed from cart!");
                break;
        }
    }
}

// Get cart items
$cartItems = getCartItems($_SESSION['user_id']);
$cartTotal = getCartTotal($_SESSION['user_id']);
?>

<div class="container">
    <h1 style="color: #2c5aa0; margin-bottom: 2rem;">Your Shopping Cart</h1>
    
    <?php if (empty($cartItems)): ?>
        <div class="cart-container">
            <div style="text-align: center; padding: 3rem;">
                <h3 style="color: #666; margin-bottom: 1rem;">Your cart is empty</h3>
                <p style="color: #999; margin-bottom: 2rem;">Browse our products and add items to your cart.</p>
                <a href="products.php" class="btn btn-primary">Shop Now</a>
            </div>
        </div>
    <?php else: ?>
        <div class="cart-container">
            <?php foreach ($cartItems as $item): ?>
                <div class="cart-item">
                    <div class="item-image">
                        <img src="<?php echo htmlspecialchars($item['image']); ?>" 
                             alt="<?php echo htmlspecialchars($item['name']); ?>"
                             onerror="this.src='https://placehold.co/80x80?text=Medicine+Product'">
                    </div>
                    
                    <div class="item-details">
                        <h3 class="item-name"><?php echo htmlspecialchars($item['name']); ?></h3>
                        <div class="item-price">Price: <?php echo formatPrice($item['price']); ?></div>
                        <div style="font-size: 0.9rem; color: #666; margin-top: 0.5rem;">
                            Stock Available: <?php echo $item['stock_quantity']; ?>
                        </div>
                    </div>
                    
                    <div class="quantity-controls">
                        <form action="cart.php" method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="update_quantity">
                            <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                            
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <button type="button" class="quantity-btn" onclick="decreaseQuantity(this)">-</button>
                                <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" 
                                       min="1" max="<?php echo $item['stock_quantity']; ?>" class="quantity-input"
                                       onchange="this.form.submit()">
                                <button type="button" class="quantity-btn" onclick="increaseQuantity(this)">+</button>
                            </div>
                        </form>
                        
                        <div style="margin-top: 1rem;">
                            <strong>Subtotal: <?php echo formatPrice($item['price'] * $item['quantity']); ?></strong>
                        </div>
                        
                        <form action="cart.php" method="POST" style="margin-top: 1rem;">
                            <input type="hidden" name="action" value="remove_item">
                            <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                            <button type="submit" class="btn" style="background: #dc3545; color: white; font-size: 0.9rem;"
                                    onclick="return confirm('Remove this item from cart?')">Remove</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="cart-summary">
            <div class="cart-total">Total: <?php echo formatPrice($cartTotal); ?></div>
            <p style="color: #666; margin-bottom: 2rem;">
                <?php echo count($cartItems); ?> item(s) in your cart
            </p>
            
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <a href="products.php" class="btn" style="background: #6c757d; color: white;">Continue Shopping</a>
                <a href="checkout.php" class="btn btn-success" style="font-size: 1.1rem; padding: 1rem 2rem;">
                    Proceed to Checkout
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function decreaseQuantity(btn) {
    const input = btn.parentNode.querySelector('input[name="quantity"]');
    const currentValue = parseInt(input.value);
    if (currentValue > 1) {
        input.value = currentValue - 1;
        input.form.submit();
    }
}

function increaseQuantity(btn) {
    const input = btn.parentNode.querySelector('input[name="quantity"]');
    const currentValue = parseInt(input.value);
    const maxValue = parseInt(input.max);
    if (currentValue < maxValue) {
        input.value = currentValue + 1;
        input.form.submit();
    }
}
</script>

<?php include 'includes/footer.php'; ?>