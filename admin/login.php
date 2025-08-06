<?php
session_start();
include '../config/database.php';

$error = '';

if ($_POST) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($username && $password) {
        try {
            // Check for plain text password first, then MD5, then SHA256
            $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();
            
            if ($admin) {
                $password_match = false;
                
                // Check plain text password
                if ($admin['password'] === $password) {
                    $password_match = true;
                }
                // Check MD5 hash
                elseif ($admin['password'] === md5($password)) {
                    $password_match = true;
                }
                // Check SHA256 hash
                elseif ($admin['password'] === hash('sha256', $password)) {
                    $password_match = true;
                }
                
                if ($password_match) {
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_username'] = $admin['username'];
                    header('Location: index.php');
                    exit();
                } else {
                    $error = 'Invalid username or password!';
                }
            } else {
                $error = 'Invalid username or password!';
            }
        } catch (Exception $e) {
            $error = 'Database connection error. Please try again.';
        }
    } else {
        $error = 'Please fill all fields!';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - CAR DECORE</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">
                    <i class="fas fa-car"></i>
                </div>
                <h2>CAR DECORE</h2>
                <p>Admin Panel Login</p>
            </div>
            
            <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" class="login-form">
                <div class="form-group">
                    <label for="username">
                        <i class="fas fa-user"></i>
                        Username
                    </label>
                    <input type="text" id="username" name="username" required value="admin" placeholder="Enter username">
                </div>
                
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i>
                        Password
                    </label>
                    <input type="password" id="password" name="password" required value="password" placeholder="Enter password">
                </div>
                
                <button type="submit" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i>
                    Login
                </button>
            </form>
            
            <div class="login-footer">
                <p>&copy; 2025 CAR DECORE. All rights reserved.</p>
                <a href="../index.php">← Back to Website</a>
                <br><br>
                <a href="bypass.php" class="bypass-link">Quick Access (Username Only)</a>
            </div>
        </div>
    </div>
</body>
</html>