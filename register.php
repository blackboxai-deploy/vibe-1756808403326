<?php
$pageTitle = "Register";
include 'includes/header.php';
require_once 'config/database.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header("Location: index.php");
    exit();
}

$errors = [];
$formData = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['username'] = sanitizeInput($_POST['username']);
    $formData['email'] = sanitizeInput($_POST['email']);
    $formData['full_name'] = sanitizeInput($_POST['full_name']);
    $formData['phone'] = sanitizeInput($_POST['phone']);
    $formData['address'] = sanitizeInput($_POST['address']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    
    // Validation
    if (empty($formData['username'])) {
        $errors[] = "Username is required.";
    } elseif (strlen($formData['username']) < 3) {
        $errors[] = "Username must be at least 3 characters long.";
    }
    
    if (empty($formData['email']) || !isValidEmail($formData['email'])) {
        $errors[] = "Valid email is required.";
    }
    
    if (empty($formData['full_name'])) {
        $errors[] = "Full name is required.";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }
    
    if ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match.";
    }
    
    // Check if username or email already exists
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$formData['username'], $formData['email']]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "Username or email already exists.";
        }
    }
    
    // If no errors, create account
    if (empty($errors)) {
        $hashedPassword = hashPassword($password);
        
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, phone, address) VALUES (?, ?, ?, ?, ?, ?)");
        
        if ($stmt->execute([$formData['username'], $formData['email'], $hashedPassword, $formData['full_name'], $formData['phone'], $formData['address']])) {
            // Auto login the user
            $userId = $pdo->lastInsertId();
            $_SESSION['user_id'] = $userId;
            $_SESSION['username'] = $formData['username'];
            $_SESSION['full_name'] = $formData['full_name'];
            
            redirectWithMessage("index.php", "Account created successfully! Welcome, " . $formData['full_name'] . "!");
        } else {
            $errors[] = "Failed to create account. Please try again.";
        }
    }
}
?>

<div class="container">
    <div class="form-container">
        <h2 style="text-align: center; color: #2c5aa0; margin-bottom: 2rem;">Create Your Account</h2>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul style="margin: 0; padding-left: 1.5rem;">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="register.php">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required 
                       value="<?php echo htmlspecialchars($formData['username'] ?? ''); ?>"
                       placeholder="Choose a unique username">
            </div>
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required 
                       value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>"
                       placeholder="Enter your email address">
            </div>
            
            <div class="form-group">
                <label for="full_name">Full Name:</label>
                <input type="text" id="full_name" name="full_name" required 
                       value="<?php echo htmlspecialchars($formData['full_name'] ?? ''); ?>"
                       placeholder="Enter your full name">
            </div>
            
            <div class="form-group">
                <label for="phone">Phone Number:</label>
                <input type="tel" id="phone" name="phone" 
                       value="<?php echo htmlspecialchars($formData['phone'] ?? ''); ?>"
                       placeholder="Enter your phone number (optional)">
            </div>
            
            <div class="form-group">
                <label for="address">Address:</label>
                <textarea id="address" name="address" rows="3" 
                          placeholder="Enter your address (optional)"><?php echo htmlspecialchars($formData['address'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required 
                       placeholder="Choose a strong password (min 6 characters)">
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required 
                       placeholder="Confirm your password">
            </div>
            
            <button type="submit" class="btn btn-primary btn-full">Create Account</button>
        </form>
        
        <div style="text-align: center; margin-top: 2rem;">
            <p>Already have an account? <a href="login.php" style="color: #2c5aa0; text-decoration: none;">Login here</a></p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>