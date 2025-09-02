<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Redirect if already logged in as admin
if (isAdminLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        // Check admin credentials
        $stmt = $pdo->prepare("SELECT admin_id, username, password, full_name, role FROM admin_users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $admin = $stmt->fetch();
        
        if ($admin && verifyPassword($password, $admin['password'])) {
            // Login successful
            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_name'] = $admin['full_name'];
            $_SESSION['admin_role'] = $admin['role'];
            
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid username or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - MediCare Store</title>
    <link rel="stylesheet" href="../css/admin.css">
    <style>
        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 2rem;
        }
        
        .login-card {
            background: white;
            padding: 3rem;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        
        .login-header {
            margin-bottom: 2rem;
        }
        
        .login-header h1 {
            color: #2c5aa0;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .login-header p {
            color: #666;
            font-size: 1rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
            text-align: left;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input {
            width: 100%;
            padding: 1rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #2c5aa0;
            box-shadow: 0 0 0 3px rgba(44,90,160,0.1);
        }
        
        .btn-login {
            width: 100%;
            padding: 1rem;
            background: #2c5aa0;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn-login:hover {
            background: #1e4080;
        }
        
        .back-link {
            margin-top: 2rem;
            text-align: center;
        }
        
        .back-link a {
            color: #2c5aa0;
            text-decoration: none;
            font-weight: 500;
        }
        
        .back-link a:hover {
            text-decoration: underline;
        }
        
        .demo-info {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            margin-top: 2rem;
            text-align: left;
        }
        
        .demo-info h4 {
            color: #2c5aa0;
            margin-bottom: 1rem;
            text-align: center;
        }
        
        .demo-info p {
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        
        .demo-info code {
            background: #e9ecef;
            padding: 0.2rem 0.4rem;
            border-radius: 3px;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>Admin Panel</h1>
                <p>MediCare Store Administration</p>
            </div>
            
            <?php if (!empty($error)): ?>
                <div style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 5px; margin-bottom: 1.5rem;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="login.php">
                <div class="form-group">
                    <label for="username">Username or Email:</label>
                    <input type="text" id="username" name="username" required 
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                           placeholder="Enter admin username or email">
                </div>
                
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required 
                           placeholder="Enter admin password">
                </div>
                
                <button type="submit" class="btn-login">Login to Admin Panel</button>
            </form>
            
            <div class="demo-info">
                <h4>Demo Admin Account</h4>
                <p><strong>Username:</strong> <code>admin</code></p>
                <p><strong>Password:</strong> <code>admin123</code></p>
            </div>
            
            <div class="back-link">
                <a href="../index.php">← Back to Main Site</a>
            </div>
        </div>
    </div>
</body>
</html>