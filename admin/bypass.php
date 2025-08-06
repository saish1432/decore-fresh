<?php
session_start();
include '../config/database.php';

$error = '';

if ($_POST) {
    $username = $_POST['username'] ?? '';
    
    if ($username) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();
            
            if ($admin) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                header('Location: index.php');
                exit();
            } else {
                $error = 'Invalid username!';
            }
        } catch (Exception $e) {
            $error = 'Database connection error. Please try again.';
        }
    } else {
        $error = 'Please enter username!';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quick Access - CAR DECORE</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">
                    <i class="fas fa-rocket"></i>
                </div>
                <h2>Quick Access</h2>
                <p>Username Only Login</p>
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
                    <input type="text" id="username" name="username" required placeholder="Enter your username" value="admin">
                </div>
                
                <button type="submit" class="login-btn">
                    <i class="fas fa-rocket"></i>
                    Quick Access
                </button>
            </form>
            
            <div class="login-footer">
                <p>&copy; 2025 CAR DECORE. All rights reserved.</p>
                <a href="login.php">← Back to Normal Login</a>
                <br>
                <a href="../index.php">← Back to Website</a>
            </div>
        </div>
    </div>
</body>
</html>