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
    
    try {
        if ($action === 'add' || $action === 'edit') {
            $id = $_POST['id'] ?? '';
            $name = $_POST['name'];
            $description = $_POST['description'];
            $price = floatval($_POST['price']);
            $market_price = floatval($_POST['market_price'] ?? 0);
            $category = $_POST['category'];
            $is_hot = isset($_POST['is_hot']) ? 1 : 0;
            $is_new = isset($_POST['is_new']) ? 1 : 0;
            $is_most_selling = isset($_POST['is_most_selling']) ? 1 : 0;
            $status = $_POST['status'];
            
            $image1 = $_POST['image1'];
            $image2 = $_POST['image2'] ?? '';
            
            if ($action === 'add') {
                $stmt = $pdo->prepare("INSERT INTO products (name, description, price, market_price, category, image1, image2, is_hot, is_new, is_most_selling, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $description, $price, $market_price, $category, $image1, $image2, $is_hot, $is_new, $is_most_selling, $status]);
                $success = 'Product added successfully!';
            } else {
                $stmt = $pdo->prepare("UPDATE products SET name=?, description=?, price=?, market_price=?, category=?, image1=?, image2=?, is_hot=?, is_new=?, is_most_selling=?, status=? WHERE id=?");
                $stmt->execute([$name, $description, $price, $market_price, $category, $image1, $image2, $is_hot, $is_new, $is_most_selling, $status, $id]);
                $success = 'Product updated successfully!';
            }
        }
        
        if ($action === 'delete') {
            $id = $_POST['id'];
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$id]);
            $success = 'Product deleted successfully!';
        }
    } catch (Exception $e) {
        $error = 'Database error: ' . $e->getMessage();
    }
}

// Get products
$products = [];
try {
    $products = $pdo->query("SELECT * FROM products ORDER BY created_at DESC")->fetchAll();
} catch (Exception $e) {
    $error = 'Error loading products: ' . $e->getMessage();
}

// Get categories for dropdown
$categories = [];
try {
    $categories = $pdo->query("SELECT * FROM categories WHERE status = 'active'")->fetchAll();
} catch (Exception $e) {
    // Continue without categories
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products Management - CAR DECORE Admin</title>
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
                <li class="active"><a href="products.php"><i class="fas fa-box"></i><span>Products</span></a></li>
                <li><a href="categories.php"><i class="fas fa-tags"></i><span>Categories</span></a></li>
                <li><a href="orders.php"><i class="fas fa-shopping-cart"></i><span>Orders</span></a></li>
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
                        <h1>Products Management</h1>
                        <p>Manage your product catalog</p>
                    </div>
                    <div class="header-actions">
                        <button class="btn btn-primary" onclick="openProductModal()">
                            <i class="fas fa-plus"></i> Add Product
                        </button>
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

                <div class="products-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Tags</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($products)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 20px;">No products found. <a href="#" onclick="openProductModal()">Add your first product</a></td>
                            </tr>
                            <?php else: ?>
                                <?php foreach($products as $product): ?>
                                <tr>
                                    <td>
                                        <img src="<?php echo $product['image1']; ?>" alt="<?php echo $product['name']; ?>" class="product-thumb" onerror="this.src='https://via.placeholder.com/50x50?text=No+Image'">
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                        <p><?php echo substr(htmlspecialchars($product['description']), 0, 50) . '...'; ?></p>
                                    </td>
                                    <td><?php echo ucfirst(htmlspecialchars($product['category'])); ?></td>
                                    <td>
                                        ₹<?php echo number_format($product['price']); ?>
                                        <?php if($product['market_price'] > $product['price']): ?>
                                        <small class="market-price">₹<?php echo number_format($product['market_price']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($product['is_hot']): ?><span class="tag hot">Hot</span><?php endif; ?>
                                        <?php if($product['is_new']): ?><span class="tag new">New</span><?php endif; ?>
                                        <?php if($product['is_most_selling']): ?><span class="tag selling">Best Seller</span><?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo $product['status']; ?>">
                                            <?php echo ucfirst($product['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-action edit" onclick="editProduct(<?php echo htmlspecialchars(json_encode($product)); ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn-action delete" onclick="deleteProduct(<?php echo $product['id']; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Modal -->
    <div id="productModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Add Product</h3>
                <span class="close" onclick="closeProductModal()">&times;</span>
            </div>
            <form id="productForm" method="POST">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="id" id="productId">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Product Name *</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="category">Category *</label>
                        <select id="category" name="category" required>
                            <option value="">Select Category</option>
                            <option value="accessories">Car Accessories</option>
                            <?php foreach($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat['name']); ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="description">Description *</label>
                    <textarea id="description" name="description" rows="3" required></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="price">Current Price *</label>
                        <input type="number" id="price" name="price" step="0.01" min="0" required>
                    </div>
                    <div class="form-group">
                        <label for="market_price">Market Price</label>
                        <input type="number" id="market_price" name="market_price" step="0.01" min="0">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="image1">Primary Image URL *</label>
                        <input type="url" id="image1" name="image1" required placeholder="https://example.com/image.jpg">
                    </div>
                    <div class="form-group">
                        <label for="image2">Secondary Image URL</label>
                        <input type="url" id="image2" name="image2" placeholder="https://example.com/image2.jpg">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Product Tags</label>
                        <div class="checkbox-group">
                            <label><input type="checkbox" name="is_hot" id="is_hot"> Hot Product</label>
                            <label><input type="checkbox" name="is_new" id="is_new"> New Arrival</label>
                            <label><input type="checkbox" name="is_most_selling" id="is_most_selling"> Most Selling</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeProductModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Product</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openProductModal() {
            document.getElementById('modalTitle').textContent = 'Add Product';
            document.getElementById('formAction').value = 'add';
            document.getElementById('productForm').reset();
            document.getElementById('productModal').style.display = 'block';
        }

        function closeProductModal() {
            document.getElementById('productModal').style.display = 'none';
        }

        function editProduct(product) {
            document.getElementById('modalTitle').textContent = 'Edit Product';
            document.getElementById('formAction').value = 'edit';
            document.getElementById('productId').value = product.id;
            document.getElementById('name').value = product.name;
            document.getElementById('description').value = product.description;
            document.getElementById('price').value = product.price;
            document.getElementById('market_price').value = product.market_price;
            document.getElementById('category').value = product.category;
            document.getElementById('image1').value = product.image1;
            document.getElementById('image2').value = product.image2;
            document.getElementById('is_hot').checked = product.is_hot == 1;
            document.getElementById('is_new').checked = product.is_new == 1;
            document.getElementById('is_most_selling').checked = product.is_most_selling == 1;
            document.getElementById('status').value = product.status;
            document.getElementById('productModal').style.display = 'block';
        }

        function deleteProduct(id) {
            if (confirm('Are you sure you want to delete this product?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('productModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                alert.style.opacity = '0';
                setTimeout(function() {
                    alert.style.display = 'none';
                }, 300);
            });
        }, 5000);
    </script>

    <style>
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: opacity 0.3s ease;
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
    </style>
</body>
</html>