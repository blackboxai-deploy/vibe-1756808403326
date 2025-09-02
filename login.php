<?php
$pageTitle = "Login";
include 'includes/header.php';
require_once 'config/database.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header("Location: index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        // Check user credentials
        $stmt = $pdo->prepare("SELECT user_id, username, password, full_name FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        
        if ($user && verifyPassword($password, $user['password'])) {
            // Login successful
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            
            // Redirect to intended page or home
            $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';
            redirectWithMessage($redirect, "Welcome back, " . $user['full_name'] . "!");
        } else {
            $error = "Invalid username or password.";
        }
    }
}
?>

<div class="container">
    <div class="form-container">
        <h2 style="text-align: center; color: #2c5aa0; margin-bottom: 2rem;">Login to Your Account</h2>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="login.php<?php echo isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : ''; ?>">
            <div class="form-group">
                <label for="username">Username or Email:</label>
                <input type="text" id="username" name="username" required 
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                       placeholder="Enter your username or email">
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required 
                       placeholder="Enter your password">
            </div>
            
            <button type="submit" class="btn btn-primary btn-full">Login</button>
        </form>
        
        <div style="text-align: center; margin-top: 2rem;">
            <p>Don't have an account? <a href="register.php" style="color: #2c5aa0; text-decoration: none;">Register here</a></p>
        </div>
        
        <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 5px; margin-top: 2rem;">
            <h4 style="color: #2c5aa0; margin-bottom: 1rem;">Demo Accounts:</h4>
            <p style="margin-bottom: 0.5rem;"><strong>User Account:</strong></p>
            <p style="font-size: 0.9rem; margin-bottom: 1rem;">Username: <code>john_doe</code> | Password: <code>user123</code></p>
            <p style="margin-bottom: 0.5rem;"><strong>Admin Account:</strong></p>
            <p style="font-size: 0.9rem;">Access via: <a href="admin/login.php" style="color: #2c5aa0;">Admin Login</a></p>
            <p style="font-size: 0.9rem;">Username: <code>admin</code> | Password: <code>admin123</code></p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>