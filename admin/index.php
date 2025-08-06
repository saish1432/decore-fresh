<?php
session_start();
include '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

try {
    // Get dashboard data with error handling
    $total_products = 0;
    $total_categories = 0;
    $total_orders = 0;
    $total_visitors = 0;
    
    try {
        $result = $pdo->query("SELECT COUNT(*) as count FROM products");
        $total_products = $result ? $result->fetch()['count'] : 0;
    } catch (Exception $e) {
        // Table might not exist, continue with 0
    }
    
    try {
        $result = $pdo->query("SELECT COUNT(*) as count FROM categories");
        $total_categories = $result ? $result->fetch()['count'] : 0;
    } catch (Exception $e) {
        // Table might not exist, continue with 0
    }
    
    try {
        $result = $pdo->query("SELECT COUNT(*) as count FROM orders");
        $total_orders = $result ? $result->fetch()['count'] : 0;
    } catch (Exception $e) {
        // Table might not exist, continue with 0
    }
    
    try {
        $result = $pdo->query("SELECT SUM(visit_count) as count FROM visitors");
        $total_visitors = $result ? ($result->fetch()['count'] ?? 0) : 0;
    } catch (Exception $e) {
        // Table might not exist, continue with 0
    }

    // Get today's orders
    $today_orders_count = 0;
    $today_revenue = 0;
    try {
        $result = $pdo->query("SELECT COUNT(*) as count, SUM(total_amount) as total FROM orders WHERE DATE(order_date) = CURDATE()");
        if ($result) {
            $today_orders = $result->fetch();
            $today_orders_count = $today_orders['count'] ?? 0;
            $today_revenue = $today_orders['total'] ?? 0;
        }
    } catch (Exception $e) {
        // Continue with default values
    }

    // Get recent orders for chart
    $recent_orders = [];
    try {
        $result = $pdo->query("
            SELECT DATE(order_date) as date, COUNT(*) as orders, SUM(total_amount) as revenue 
            FROM orders 
            WHERE order_date >= DATE_SUB(NOW(), INTERVAL 30 DAYS) 
            GROUP BY DATE(order_date) 
            ORDER BY date ASC
        ");
        if ($result) {
            $recent_orders = $result->fetchAll();
        }
    } catch (Exception $e) {
        // Continue with empty array
    }

    // Get monthly data
    $monthly_data = [];
    try {
        $result = $pdo->query("
            SELECT 
                YEAR(order_date) as year,
                MONTH(order_date) as month,
                COUNT(*) as orders,
                SUM(total_amount) as revenue
            FROM orders 
            WHERE order_date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY YEAR(order_date), MONTH(order_date)
            ORDER BY year ASC, month ASC
        ");
        if ($result) {
            $monthly_data = $result->fetchAll();
        }
    } catch (Exception $e) {
        // Continue with empty array
    }

    // Get recent orders list
    $recent_orders_list = [];
    try {
        $result = $pdo->query("SELECT * FROM orders ORDER BY order_date DESC LIMIT 10");
        if ($result) {
            $recent_orders_list = $result->fetchAll();
        }
    } catch (Exception $e) {
        // Continue with empty array
    }

} catch (Exception $e) {
    // If there's a major database error, set default values
    $total_products = 0;
    $total_categories = 0;
    $total_orders = 0;
    $total_visitors = 0;
    $today_orders_count = 0;
    $today_revenue = 0;
    $recent_orders = [];
    $monthly_data = [];
    $recent_orders_list = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - CAR DECORE</title>
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
                <li class="active">
                    <a href="index.php">
                        <i class="fas fa-dashboard"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="products.php">
                        <i class="fas fa-box"></i>
                        <span>Products</span>
                    </a>
                </li>
                <li>
                    <a href="categories.php">
                        <i class="fas fa-tags"></i>
                        <span>Categories</span>
                    </a>
                </li>
                <li>
                    <a href="orders.php">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Orders</span>
                    </a>
                </li>
                <li>
                    <a href="videos.php">
                        <i class="fas fa-video"></i>
                        <span>Videos</span>
                    </a>
                </li>
                <li>
                    <a href="analytics.php">
                        <i class="fas fa-chart-line"></i>
                        <span>Analytics</span>
                    </a>
                </li>
                <li>
                    <a href="settings.php">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </li>
                <li>
                    <a href="logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <header class="main-header">
                <div class="header-content">
                    <div class="header-title">
                        <h1>Dashboard</h1>
                        <p>Welcome back, <?php echo $_SESSION['admin_username'] ?? 'Admin'; ?>!</p>
                    </div>
                    <div class="header-actions">
                        <span class="datetime" id="currentDateTime"></span>
                        <a href="../index.php" class="view-site-btn" target="_blank">
                            <i class="fas fa-external-link-alt"></i>
                            View Site
                        </a>
                    </div>
                </div>
            </header>

            <!-- Dashboard Content -->
            <div class="dashboard-content">
                <!-- Stats Cards -->
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
                            <i class="fas fa-tags"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo number_format($total_categories); ?></h3>
                            <p>Categories</p>
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
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo number_format($total_visitors); ?></h3>
                            <p>Total Visitors</p>
                        </div>
                    </div>
                </div>

                <!-- Today's Stats -->
                <div class="today-stats">
                    <div class="today-card">
                        <h3>Today's Performance</h3>
                        <div class="today-grid">
                            <div class="today-item">
                                <i class="fas fa-shopping-bag"></i>
                                <div>
                                    <span class="today-number"><?php echo $today_orders_count; ?></span>
                                    <span class="today-label">Orders</span>
                                </div>
                            </div>
                            <div class="today-item">
                                <i class="fas fa-rupee-sign"></i>
                                <div>
                                    <span class="today-number">₹<?php echo number_format($today_revenue); ?></span>
                                    <span class="today-label">Revenue</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="charts-section">
                    <div class="chart-container">
                        <div class="chart-header">
                            <h3>Sales Overview (Last 30 Days)</h3>
                        </div>
                        <canvas id="salesChart"></canvas>
                    </div>
                    
                    <div class="chart-container">
                        <div class="chart-header">
                            <h3>Monthly Revenue (Last 12 Months)</h3>
                        </div>
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="recent-activity">
                    <h3>Recent Orders</h3>
                    <div class="activity-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Date</th>
                                    <th>Items</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recent_orders_list)): ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 20px;">No orders found</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach($recent_orders_list as $order): ?>
                                    <tr>
                                        <td>#<?php echo $order['id']; ?></td>
                                        <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                                        <td><?php echo $order['items_count']; ?> items</td>
                                        <td>₹<?php echo number_format($order['total_amount']); ?></td>
                                        <td><span class="status-badge status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Update current date and time
        function updateDateTime() {
            const now = new Date();
            const options = { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric', 
                hour: '2-digit', 
                minute: '2-digit' 
            };
            document.getElementById('currentDateTime').textContent = now.toLocaleDateString('en-US', options);
        }
        
        updateDateTime();
        setInterval(updateDateTime, 60000);

        // Sales Chart
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        const salesData = <?php echo json_encode($recent_orders); ?>;
        
        new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: salesData.map(item => {
                    const date = new Date(item.date);
                    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                }),
                datasets: [{
                    label: 'Orders',
                    data: salesData.map(item => item.orders),
                    borderColor: '#e74c3c',
                    backgroundColor: 'rgba(231, 76, 60, 0.1)',
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
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // Revenue Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        const monthlyData = <?php echo json_encode($monthly_data); ?>;
        
        new Chart(revenueCtx, {
            type: 'bar',
            data: {
                labels: monthlyData.map(item => {
                    const date = new Date(item.year, item.month - 1);
                    return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
                }),
                datasets: [{
                    label: 'Revenue (₹)',
                    data: monthlyData.map(item => item.revenue),
                    backgroundColor: 'rgba(52, 152, 219, 0.8)',
                    borderColor: '#3498db',
                    borderWidth: 1
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