<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

// Handle status update
if ($_POST && isset($_POST['action'])) {
    $action = $_POST['action'];
    $order_id = $_POST['order_id'];
    
    if ($action === 'update_status') {
        $status = $_POST['status'];
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$status, $order_id]);
        header('Location: orders.php');
        exit();
    }
    
    if ($action === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
        $stmt->execute([$order_id]);
        header('Location: orders.php');
        exit();
    }
}

// Get orders with pagination
$page = $_GET['page'] ?? 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$orders = $pdo->query("SELECT * FROM orders ORDER BY order_date DESC LIMIT $limit OFFSET $offset")->fetchAll();
$total_orders = $pdo->query("SELECT COUNT(*) as count FROM orders")->fetch()['count'];
$total_pages = ceil($total_orders / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Management - CAR DECORE Admin</title>
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
                <li class="active"><a href="orders.php"><i class="fas fa-shopping-cart"></i><span>Orders</span></a></li>
                <li><a href="videos.php"><i class="fas fa-video"></i><span>Videos</span></a></li>
                <li><a href="analytics.php"><i class="fas fa-chart-line"></i><span>Analytics</span></a></li>
                <li><a href="settings.php"><i class="fas fa-cog"></i><span>Settings</span></a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="main-content">
            <header class="main-header">
                <div class="header-content">
                    <div class="header-title">
                        <h1>Orders Management</h1>
                        <p>Track and manage customer orders</p>
                    </div>
                    <div class="header-actions">
                        <span class="total-orders">Total Orders: <?php echo number_format($total_orders); ?></span>
                    </div>
                </div>
            </header>

            <div class="content-area">
                <div class="products-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Date</th>
                                <th>Items</th>
                                <th>Total Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($orders as $order): ?>
                            <tr>
                                <td><strong>#<?php echo $order['id']; ?></strong></td>
                                <td><?php echo date('M d, Y H:i', strtotime($order['order_date'])); ?></td>
                                <td>
                                    <button class="btn-link" onclick="viewOrderDetails(<?php echo $order['id']; ?>, '<?php echo htmlspecialchars($order['order_items']); ?>')">
                                        <?php echo $order['items_count']; ?> items
                                    </button>
                                </td>
                                <td><strong>₹<?php echo number_format($order['total_amount']); ?></strong></td>
                                <td>
                                    <select onchange="updateOrderStatus(<?php echo $order['id']; ?>, this.value)" class="status-select status-<?php echo $order['status']; ?>">
                                        <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                        <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-action view" onclick="viewOrderDetails(<?php echo $order['id']; ?>, '<?php echo htmlspecialchars($order['order_items']); ?>')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn-action delete" onclick="deleteOrder(<?php echo $order['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>" class="<?php echo $page == $i ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div id="orderModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="orderModalTitle">Order Details</h3>
                <span class="close" onclick="closeOrderModal()">&times;</span>
            </div>
            <div id="orderDetails" class="order-details">
                <!-- Order details will be loaded here -->
            </div>
        </div>
    </div>

    <script>
        function updateOrderStatus(orderId, status) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="order_id" value="${orderId}">
                <input type="hidden" name="status" value="${status}">
            `;
            document.body.appendChild(form);
            form.submit();
        }

        function deleteOrder(orderId) {
            if (confirm('Are you sure you want to delete this order?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="order_id" value="${orderId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function viewOrderDetails(orderId, orderItems) {
            document.getElementById('orderModalTitle').textContent = `Order #${orderId} Details`;
            
            try {
                const items = JSON.parse(orderItems);
                let detailsHTML = '<div class="order-items">';
                
                items.forEach(item => {
                    detailsHTML += `
                        <div class="order-item">
                            <h4>${item.name}</h4>
                            <p>Quantity: ${item.quantity || 1}</p>
                            <p>Price: ₹${item.price}</p>
                            <p>Total: ₹${(item.price * (item.quantity || 1)).toFixed(2)}</p>
                        </div>
                    `;
                });
                
                detailsHTML += '</div>';
                document.getElementById('orderDetails').innerHTML = detailsHTML;
            } catch (e) {
                document.getElementById('orderDetails').innerHTML = '<p>Error loading order details</p>';
            }
            
            document.getElementById('orderModal').style.display = 'block';
        }

        function closeOrderModal() {
            document.getElementById('orderModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('orderModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>

    <style>
        .status-select {
            padding: 5px 10px;
            border-radius: 15px;
            border: none;
            font-size: 0.8rem;
            font-weight: 500;
            cursor: pointer;
        }

        .status-select.status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-select.status-completed {
            background: #d4edda;
            color: #155724;
        }

        .status-select.status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        .btn-link {
            background: none;
            border: none;
            color: var(--secondary-color);
            cursor: pointer;
            text-decoration: underline;
        }

        .btn-link:hover {
            color: var(--primary-color);
        }

        .total-orders {
            background: var(--success-color);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 5px;
            margin-top: 20px;
        }

        .pagination a {
            padding: 8px 12px;
            border: 1px solid #ddd;
            color: var(--primary-color);
            text-decoration: none;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .pagination a:hover,
        .pagination a.active {
            background: var(--secondary-color);
            color: white;
            border-color: var(--secondary-color);
        }

        .order-details {
            padding: 20px;
        }

        .order-item {
            background: var(--gray-light);
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 8px;
        }

        .order-item h4 {
            margin: 0 0 10px 0;
            color: var(--primary-color);
        }

        .order-item p {
            margin: 5px 0;
            color: var(--gray-medium);
        }

        .btn-action.view {
            background: var(--secondary-color);
            color: white;
        }

        .btn-action.view:hover {
            background: #2980b9;
        }
    </style>
</body>
</html>