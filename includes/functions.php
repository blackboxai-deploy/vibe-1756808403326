<?php
// Common functions for Medicine Store

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Function to sanitize input data
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Function to check if admin is logged in
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

// Function to redirect with message
function redirectWithMessage($url, $message, $type = 'success') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
    header("Location: $url");
    exit();
}

// Function to display flash messages
function displayMessage() {
    if (isset($_SESSION['message'])) {
        $type = $_SESSION['message_type'] ?? 'success';
        $alertClass = ($type === 'error') ? 'alert-error' : 'alert-success';
        
        echo "<div class='alert $alertClass'>";
        echo htmlspecialchars($_SESSION['message']);
        echo "</div>";
        
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    }
}

// Function to format currency
function formatPrice($price) {
    return '$' . number_format($price, 2);
}

// Function to get cart count
function getCartCount() {
    if (!isLoggedIn()) {
        return 0;
    }
    
    require_once 'config/database.php';
    $stmt = $pdo->prepare("SELECT SUM(quantity) FROM cart WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetchColumn() ?? 0;
}

// Function to add item to cart
function addToCart($userId, $medicineId, $quantity = 1) {
    require_once 'config/database.php';
    
    // Check if item already exists in cart
    $stmt = $pdo->prepare("SELECT cart_id, quantity FROM cart WHERE user_id = ? AND medicine_id = ?");
    $stmt->execute([$userId, $medicineId]);
    $existingItem = $stmt->fetch();
    
    if ($existingItem) {
        // Update quantity
        $newQuantity = $existingItem['quantity'] + $quantity;
        $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ?");
        return $stmt->execute([$newQuantity, $existingItem['cart_id']]);
    } else {
        // Insert new item
        $stmt = $pdo->prepare("INSERT INTO cart (user_id, medicine_id, quantity) VALUES (?, ?, ?)");
        return $stmt->execute([$userId, $medicineId, $quantity]);
    }
}

// Function to get cart items
function getCartItems($userId) {
    require_once 'config/database.php';
    
    $stmt = $pdo->prepare("
        SELECT c.cart_id, c.quantity, m.medicine_id, m.name, m.price, m.image, m.stock_quantity
        FROM cart c 
        JOIN medicines m ON c.medicine_id = m.medicine_id 
        WHERE c.user_id = ?
        ORDER BY c.added_at DESC
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

// Function to calculate cart total
function getCartTotal($userId) {
    require_once 'config/database.php';
    
    $stmt = $pdo->prepare("
        SELECT SUM(c.quantity * m.price) as total
        FROM cart c 
        JOIN medicines m ON c.medicine_id = m.medicine_id 
        WHERE c.user_id = ?
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchColumn() ?? 0;
}

// Function to clear cart
function clearCart($userId) {
    require_once 'config/database.php';
    
    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
    return $stmt->execute([$userId]);
}

// Function to validate email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Function to hash password
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Function to verify password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}
?>