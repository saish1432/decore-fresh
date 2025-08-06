<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

// Handle form submissions
if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add' || $action === 'edit') {
        $id = $_POST['id'] ?? '';
        $name = $_POST['name'];
        $slug = strtolower(str_replace(' ', '-', $name));
        $logo = $_POST['logo'];
        $description = $_POST['description'];
        $status = $_POST['status'];
        
        if ($action === 'add') {
            $stmt = $pdo->prepare("INSERT INTO categories (name, slug, logo, description, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $slug, $logo, $description, $status]);
        } else {
            $stmt = $pdo->prepare("UPDATE categories SET name=?, slug=?, logo=?, description=?, status=? WHERE id=?");
            $stmt->execute([$name, $slug, $logo, $description, $status, $id]);
        }
        
        header('Location: categories.php');
        exit();
    }
    
    if ($action === 'delete') {
        $id = $_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        
        header('Location: categories.php');
        exit();
    }
}

// Get categories
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories Management - CAR DECORE Admin</title>
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
                <li class="active"><a href="categories.php"><i class="fas fa-tags"></i><span>Categories</span></a></li>
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
                        <h1>Categories Management</h1>
                        <p>Manage car brand categories</p>
                    </div>
                    <div class="header-actions">
                        <button class="btn btn-primary" onclick="openCategoryModal()">
                            <i class="fas fa-plus"></i> Add Category
                        </button>
                    </div>
                </div>
            </header>

            <div class="content-area">
                <div class="products-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Logo</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($categories as $category): ?>
                            <tr>
                                <td>
                                    <img src="<?php echo $category['logo']; ?>" alt="<?php echo $category['name']; ?>" class="product-thumb">
                                </td>
                                <td>
                                    <strong><?php echo $category['name']; ?></strong>
                                </td>
                                <td><?php echo $category['description']; ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $category['status']; ?>">
                                        <?php echo ucfirst($category['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($category['created_at'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-action edit" onclick="editCategory(<?php echo htmlspecialchars(json_encode($category)); ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn-action delete" onclick="deleteCategory(<?php echo $category['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Modal -->
    <div id="categoryModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Add Category</h3>
                <span class="close" onclick="closeCategoryModal()">&times;</span>
            </div>
            <form id="categoryForm" method="POST">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="id" id="categoryId">
                
                <div class="form-group">
                    <label for="name">Category Name *</label>
                    <input type="text" id="name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="logo">Logo URL *</label>
                    <input type="url" id="logo" name="logo" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeCategoryModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Category</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openCategoryModal() {
            document.getElementById('modalTitle').textContent = 'Add Category';
            document.getElementById('formAction').value = 'add';
            document.getElementById('categoryForm').reset();
            document.getElementById('categoryModal').style.display = 'block';
        }

        function closeCategoryModal() {
            document.getElementById('categoryModal').style.display = 'none';
        }

        function editCategory(category) {
            document.getElementById('modalTitle').textContent = 'Edit Category';
            document.getElementById('formAction').value = 'edit';
            document.getElementById('categoryId').value = category.id;
            document.getElementById('name').value = category.name;
            document.getElementById('logo').value = category.logo;
            document.getElementById('description').value = category.description;
            document.getElementById('status').value = category.status;
            document.getElementById('categoryModal').style.display = 'block';
        }

        function deleteCategory(id) {
            if (confirm('Are you sure you want to delete this category?')) {
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
            const modal = document.getElementById('categoryModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>