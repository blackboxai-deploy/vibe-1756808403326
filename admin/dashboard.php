<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if admin is logged in
if (!isAdminLoggedIn()) {
    header("Location: login.php");
    exit();
}

// Get dashboard statistics
$stats = [];

// Total medicines
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM medicines");
$stmt->execute();
$stats['medicines'] = $stmt->fetch()['total'];

// Total users
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM users");
$stmt->execute();
$stats['users'] = $stmt->fetch()['total'];

// Total orders
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM orders");
$stmt->execute();
$stats['orders'] = $stmt->fetch()['total'];

// Total revenue
$stmt = $pdo->prepare("SELECT SUM(total_amount) as revenue FROM orders WHERE status != 'cancelled'");
$stmt->execute();
$stats['revenue'] = $stmt->fetch()['revenue'] ?? 0;

// Recent orders
$stmt = $pdo->prepare("
    SELECT o.order_id, o.total_amount, o.status, o.order_date, u.full_name 
    FROM orders o 
    JOIN users u ON o.user_id = u.user_id 
    ORDER BY o.order_date DESC 
    LIMIT 5
");
$stmt->execute();
$recentOrders = $stmt->fetchAll();

// Low stock products
$stmt = $pdo->prepare("SELECT * FROM medicines WHERE stock_quantity <= 10 ORDER BY stock_quantity ASC LIMIT 5");
$stmt->execute();
$lowStockProducts = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - MediCare Store</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="admin-header">
                <h1>MediCare Admin</h1>
                <p>Management Panel</p>
            </div>
            
            <nav class="admin-nav">
                <ul>
                    <li><a href="dashboard.php" class="active"><span class="nav-icon">📊</span> Dashboard</a></li>
                    <li><a href="manage-products.php"><span class="nav-icon">💊</span> Products</a></li>
                    <li><a href="manage-orders.php"><span class="nav-icon">📦</span> Orders</a></li>
                    <li><a href="manage-users.php"><span class="nav-icon">👥</span> Users</a></li>
                    <li><a href="add-product.php"><span class="nav-icon">➕</span> Add Product</a></li>
                    <li><a href="../index.php" target="_blank"><span class="nav-icon">🌐</span> View Site</a></li>
                    <li><a href="logout.php"><span class="nav-icon">🚪</span> Logout</a></li>
                </ul>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <main class="admin-main">
            <div class="admin-main-header">
                <h1>Dashboard</h1>
                <div class="admin-user-info">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?>!</span>
                    <a href="logout.php" class="admin-logout">Logout</a>
                </div>
            </div>
            
            <!-- Statistics Cards -->
            <div class="dashboard-grid">
                <div class="dashboard-card card-primary">
                    <div class="card-icon primary">💊</div>
                    <div class="card-number"><?php echo number_format($stats['medicines']); ?></div>
                    <div class="card-title">Total Products</div>
                    <a href="manage-products.php" class="card-link">View Products →</a>
                </div>
                
                <div class="dashboard-card card-success">
                    <div class="card-icon success">💰</div>
                    <div class="card-number"><?php echo formatPrice($stats['revenue']); ?></div>
                    <div class="card-title">Total Revenue</div>
                    <a href="manage-orders.php" class="card-link">View Orders →</a>
                </div>
                
                <div class="dashboard-card card-info">
                    <div class="card-icon info">📦</div>
                    <div class="card-number"><?php echo number_format($stats['orders']); ?></div>
                    <div class="card-title">Total Orders</div>
                    <a href="manage-orders.php" class="card-link">View Orders →</a>
                </div>
                
                <div class="dashboard-card card-warning">
                    <div class="card-icon warning">👥</div>
                    <div class="card-number"><?php echo number_format($stats['users']); ?></div>
                    <div class="card-title">Registered Users</div>
                    <a href="manage-users.php" class="card-link">View Users →</a>
                </div>
            </div>
            
            <!-- Recent Orders and Low Stock -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-top: 2rem;">
                
                <!-- Recent Orders -->
                <div class="admin-content">
                    <div class="content-header">
                        <h2>Recent Orders</h2>
                        <a href="manage-orders.php" class="btn btn-primary btn-sm">View All</a>
                    </div>
                    <div class="content-body">
                        <?php if (empty($recentOrders)): ?>
                            <p style="text-align: center; color: #666; padding: 2rem;">No orders found.</p>
                        <?php else: ?>
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Customer</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentOrders as $order): ?>
                                        <tr>
                                            <td>#<?php echo $order['order_id']; ?></td>
                                            <td><?php echo htmlspecialchars($order['full_name']); ?></td>
                                            <td><?php echo formatPrice($order['total_amount']); ?></td>
                                            <td>
                                                <span class="status-badge status-<?php echo $order['status']; ?>">
                                                    <?php echo ucfirst($order['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M j, Y', strtotime($order['order_date'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Low Stock Alert -->
                <div class="admin-content">
                    <div class="content-header">
                        <h2>Low Stock Alert</h2>
                        <a href="manage-products.php" class="btn btn-warning btn-sm">Manage Stock</a>
                    </div>
                    <div class="content-body">
                        <?php if (empty($lowStockProducts)): ?>
                            <p style="text-align: center; color: #666; padding: 2rem;">All products are well stocked!</p>
                        <?php else: ?>
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Stock</th>
                                        <th>Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($lowStockProducts as $product): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                                <br>
                                                <small style="color: #666;"><?php echo htmlspecialchars($product['manufacturer']); ?></small>
                                            </td>
                                            <td>
                                                <span style="color: <?php echo ($product['stock_quantity'] == 0) ? '#dc3545' : '#ffc107'; ?>; font-weight: 600;">
                                                    <?php echo $product['stock_quantity']; ?> units
                                                </span>
                                            </td>
                                            <td><?php echo formatPrice($product['price']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="admin-content" style="margin-top: 2rem;">
                <div class="content-header">
                    <h2>Quick Actions</h2>
                </div>
                <div class="content-body">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                        <a href="add-product.php" class="btn btn-success" style="padding: 1.5rem; text-align: center;">
                            <div style="font-size: 2rem; margin-bottom: 0.5rem;">➕</div>
                            Add New Product
                        </a>
                        
                        <a href="manage-orders.php?status=pending" class="btn btn-warning" style="padding: 1.5rem; text-align: center;">
                            <div style="font-size: 2rem; margin-bottom: 0.5rem;">⏳</div>
                            Pending Orders
                        </a>
                        
                        <a href="manage-products.php?filter=low_stock" class="btn btn-danger" style="padding: 1.5rem; text-align: center;">
                            <div style="font-size: 2rem; margin-bottom: 0.5rem;">⚠️</div>
                            Low Stock Items
                        </a>
                        
                        <a href="manage-users.php" class="btn btn-primary" style="padding: 1.5rem; text-align: center;">
                            <div style="font-size: 2rem; margin-bottom: 0.5rem;">👥</div>
                            Manage Users
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>