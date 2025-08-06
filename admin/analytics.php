<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

// Get analytics data
$total_products = $pdo->query("SELECT COUNT(*) as count FROM products")->fetch()['count'];
$total_orders = $pdo->query("SELECT COUNT(*) as count FROM orders")->fetch()['count'];
$total_revenue = $pdo->query("SELECT SUM(total_amount) as total FROM orders WHERE status = 'completed'")->fetch()['total'] ?? 0;
$total_visitors = $pdo->query("SELECT SUM(visit_count) as count FROM visitors")->fetch()['count'] ?? 0;

// Get device breakdown
$device_stats = $pdo->query("
    SELECT device_type, SUM(visit_count) as count 
    FROM visitors 
    GROUP BY device_type
")->fetchAll();

// Get monthly revenue
$monthly_revenue = $pdo->query("
    SELECT 
        YEAR(order_date) as year,
        MONTH(order_date) as month,
        SUM(total_amount) as revenue,
        COUNT(*) as orders
    FROM orders 
    WHERE order_date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY YEAR(order_date), MONTH(order_date)
    ORDER BY year ASC, month ASC
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - CAR DECORE Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <li class="active"><a href="analytics.php"><i class="fas fa-chart-line"></i><span>Analytics</span></a></li>
                <li><a href="settings.php"><i class="fas fa-cog"></i><span>Settings</span></a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="main-content">
            <header class="main-header">
                <div class="header-content">
                    <div class="header-title">
                        <h1>Analytics</h1>
                        <p>Detailed website and sales analytics</p>
                    </div>
                </div>
            </header>

            <div class="content-area">
                <!-- Stats Overview -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-box"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo number_format($total_products); ?></h3>
                            <p>Total Products</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo number_format($total_orders); ?></h3>
                            <p>Total Orders</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-rupee-sign"></i>
                        </div>
                        <div class="stat-info">
                            <h3>₹<?php echo number_format($total_revenue); ?></h3>
                            <p>Total Revenue</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo number_format($total_visitors); ?></h3>
                            <p>Total Visitors</p>
                        </div>
                    </div>
                </div>

                <!-- Charts -->
                <div class="charts-section">
                    <div class="chart-container">
                        <div class="chart-header">
                            <h3>Device Breakdown</h3>
                        </div>
                        <canvas id="deviceChart"></canvas>
                    </div>
                    
                    <div class="chart-container">
                        <div class="chart-header">
                            <h3>Monthly Revenue</h3>
                        </div>
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Device Chart
        const deviceCtx = document.getElementById('deviceChart').getContext('2d');
        const deviceData = <?php echo json_encode($device_stats); ?>;
        
        new Chart(deviceCtx, {
            type: 'doughnut',
            data: {
                labels: deviceData.map(item => item.device_type.charAt(0).toUpperCase() + item.device_type.slice(1)),
                datasets: [{
                    data: deviceData.map(item => item.count),
                    backgroundColor: ['#3498db', '#e74c3c', '#f39c12'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Revenue Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        const monthlyData = <?php echo json_encode($monthly_revenue); ?>;
        
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: monthlyData.map(item => {
                    const date = new Date(item.year, item.month - 1);
                    return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
                }),
                datasets: [{
                    label: 'Revenue (₹)',
                    data: monthlyData.map(item => item.revenue),
                    borderColor: '#27ae60',
                    backgroundColor: 'rgba(39, 174, 96, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₹' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>