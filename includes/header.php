<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>MediCare Store</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="nav-brand">
                <h1><a href="index.php">MediCare Store</a></h1>
                <p>Your Trusted Online Pharmacy</p>
            </div>
            
            <nav class="nav-menu">
                <ul>
                    <li><a href="index.php" class="nav-link">Home</a></li>
                    <li><a href="products.php" class="nav-link">Products</a></li>
                    
                    <?php if (isLoggedIn()): ?>
                        <li><a href="cart.php" class="nav-link">Cart (<?php echo getCartCount(); ?>)</a></li>
                        <li><a href="orders.php" class="nav-link">My Orders</a></li>
                        <li><a href="logout.php" class="nav-link">Logout</a></li>
                        <li class="user-greeting">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</li>
                    <?php else: ?>
                        <li><a href="login.php" class="nav-link">Login</a></li>
                        <li><a href="register.php" class="nav-link">Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
            
            <div class="search-box">
                <form action="products.php" method="GET" class="search-form">
                    <input type="text" name="search" placeholder="Search medicines..." 
                           value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <button type="submit" class="search-btn">Search</button>
                </form>
            </div>
        </div>
    </header>

    <main class="main-content">
        <?php displayMessage(); ?>