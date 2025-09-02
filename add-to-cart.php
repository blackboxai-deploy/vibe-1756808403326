<?php
session_start();
require_once 'includes/functions.php';
require_once 'config/database.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirectWithMessage("login.php", "Please login to add items to cart.", "error");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['medicine_id'])) {
    $medicineId = (int)$_POST['medicine_id'];
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    
    // Validate quantity
    if ($quantity < 1) {
        $quantity = 1;
    }
    
    // Check if medicine exists and has stock
    $stmt = $pdo->prepare("SELECT medicine_id, name, stock_quantity FROM medicines WHERE medicine_id = ?");
    $stmt->execute([$medicineId]);
    $medicine = $stmt->fetch();
    
    if (!$medicine) {
        redirectWithMessage("products.php", "Medicine not found.", "error");
    }
    
    if ($medicine['stock_quantity'] < $quantity) {
        redirectWithMessage("products.php", "Insufficient stock for " . $medicine['name'] . ".", "error");
    }
    
    // Add to cart
    if (addToCart($_SESSION['user_id'], $medicineId, $quantity)) {
        redirectWithMessage("cart.php", "Added " . $medicine['name'] . " to cart successfully!");
    } else {
        redirectWithMessage("products.php", "Failed to add item to cart. Please try again.", "error");
    }
} else {
    redirectWithMessage("products.php", "Invalid request.", "error");
}
?>