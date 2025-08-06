<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

$success = '';
$error = '';

// Handle form submissions
if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'change_password') {
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if ($new_password === $confirm_password) {
            $stmt = $pdo->prepare("UPDATE admin_users SET password = ? WHERE id = ?");
            $stmt->execute([md5($new_password), $_SESSION['admin_id']]);
            $success = 'Password changed successfully!';
        } else {
            $error = 'Passwords do not match!';
        }
    }
    
    if ($action === 'change_username') {
        $new_username = $_POST['new_username'];
        
        // Check if username already exists
        $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE username = ? AND id != ?");
        $stmt->execute([$new_username, $_SESSION['admin_id']]);
        
        if ($stmt->fetch()) {
            $error = 'Username already exists!';
        } else {
            $stmt = $pdo->prepare("UPDATE admin_users SET username = ? WHERE id = ?");
            $stmt->execute([$new_username, $_SESSION['admin_id']]);
            $_SESSION['admin_username'] = $new_username;
            $success = 'Username changed successfully!';
        }
    }
    
    if ($action === 'update_settings') {
        $whatsapp_number = $_POST['whatsapp_number'];
        $site_title = $_POST['site_title'];
        $office_address = $_POST['office_address'];
        
        $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'whatsapp_number'");
        $stmt->execute([$whatsapp_number]);
        
        $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'site_title'");
        $stmt->execute([$site_title]);
        
        $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'office_address'");
        $stmt->execute([$office_address]);
        
        $success = 'Settings updated successfully!';
    }
}

// Get current admin info
$stmt = $pdo->prepare("SELECT * FROM admin_users WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$admin = $stmt->fetch();

// Get current settings
$settings = [];
$stmt = $pdo->query("SELECT * FROM settings");
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - CAR DECORE Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-car"></i> CAR DECORE</h2>
                <p>Admin Panel</p>
            </div>
            <ul class="sidebar-menu">
                <li><a href="index.php"><i class="fas fa-dashboard"></i><span>Dashboard</span></a></li>
                <li><a href="products.php"><i class="fas fa-box"></i><span>Products</span></a></li>
                <li><a href="categories.php"><i class="fas fa-tags"></i><span>Categories</span></a></li>
                <li><a href="orders.php"><i class="fas fa-shopping-cart"></i><span>Orders</span></a></li>
                <li><a href="videos.php"><i class="fas fa-video"></i><span>Videos</span></a></li>
                <li><a href="analytics.php"><i class="fas fa-chart-line"></i><span>Analytics</span></a></li>
                <li class="active"><a href="settings.php"><i class="fas fa-cog"></i><span>Settings</span></a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="main-content">
            <header class="main-header">
                <div class="header-content">
                    <div class="header-title">
                        <h1>Settings</h1>
                        <p>Manage admin account and website settings</p>
                    </div>
                    <div class="header-actions">
                        <span class="admin-info">Welcome, <?php echo $_SESSION['admin_username']; ?>!</span>
                    </div>
                </div>
            </header>

            <div class="content-area">
                <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success; ?>
                </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo $error; ?>
                </div>
                <?php endif; ?>

                <div class="settings-grid">
                    <!-- Admin Account Settings -->
                    <div class="settings-card">
                        <h3><i class="fas fa-user-cog"></i> Admin Account</h3>
                        
                        <!-- Change Username -->
                        <form method="POST" class="settings-form">
                            <input type="hidden" name="action" value="change_username">
                            <div class="form-group">
                                <label for="new_username">Change Username</label>
                                <input type="text" id="new_username" name="new_username" value="<?php echo $admin['username']; ?>" required>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Username
                            </button>
                        </form>
                        
                        <hr>
                        
                        <!-- Change Password -->
                        <form method="POST" class="settings-form">
                            <input type="hidden" name="action" value="change_password">
                            <div class="form-group">
                                <label for="new_password">New Password</label>
                                <input type="password" id="new_password" name="new_password" required>
                            </div>
                            <div class="form-group">
                                <label for="confirm_password">Confirm Password</label>
                                <input type="password" id="confirm_password" name="confirm_password" required>
                            </div>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-key"></i> Change Password
                            </button>
                        </form>
                    </div>

                    <!-- Website Settings -->
                    <div class="settings-card">
                        <h3><i class="fas fa-globe"></i> Website Settings</h3>
                        
                        <form method="POST" class="settings-form">
                            <input type="hidden" name="action" value="update_settings">
                            
                            <div class="form-group">
                                <label for="whatsapp_number">WhatsApp Number</label>
                                <input type="text" id="whatsapp_number" name="whatsapp_number" value="<?php echo $settings['whatsapp_number'] ?? ''; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="site_title">Site Title</label>
                                <input type="text" id="site_title" name="site_title" value="<?php echo $settings['site_title'] ?? ''; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="office_address">Office Address</label>
                                <textarea id="office_address" name="office_address" rows="3" required><?php echo $settings['office_address'] ?? ''; ?></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Settings
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .admin-info {
            background: var(--success-color);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .settings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 30px;
        }

        .settings-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .settings-card h3 {
            color: var(--primary-color);
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .settings-form {
            margin-bottom: 20px;
        }

        .settings-form hr {
            margin: 25px 0;
            border: none;
            border-top: 1px solid #eee;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        @media (max-width: 768px) {
            .settings-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>
</html>