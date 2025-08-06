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
        $title = $_POST['title'];
        $description = $_POST['description'];
        $video_path = $_POST['video_path'];
        $thumbnail = $_POST['thumbnail'];
        $status = $_POST['status'];
        
        if ($action === 'add') {
            $stmt = $pdo->prepare("INSERT INTO videos (title, description, video_path, thumbnail, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$title, $description, $video_path, $thumbnail, $status]);
        } else {
            $stmt = $pdo->prepare("UPDATE videos SET title=?, description=?, video_path=?, thumbnail=?, status=? WHERE id=?");
            $stmt->execute([$title, $description, $video_path, $thumbnail, $status, $id]);
        }
        
        header('Location: videos.php');
        exit();
    }
    
    if ($action === 'delete') {
        $id = $_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM videos WHERE id = ?");
        $stmt->execute([$id]);
        
        header('Location: videos.php');
        exit();
    }
}

// Get videos
$videos = $pdo->query("SELECT * FROM videos ORDER BY created_at DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Videos Management - CAR DECORE Admin</title>
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
                <li class="active"><a href="videos.php"><i class="fas fa-video"></i><span>Videos</span></a></li>
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
                        <h1>Videos Management</h1>
                        <p>Manage product demo videos</p>
                    </div>
                    <div class="header-actions">
                        <button class="btn btn-primary" onclick="openVideoModal()">
                            <i class="fas fa-plus"></i> Add Video
                        </button>
                    </div>
                </div>
            </header>

            <div class="content-area">
                <div class="products-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Thumbnail</th>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($videos as $video): ?>
                            <tr>
                                <td>
                                    <img src="<?php echo $video['thumbnail']; ?>" alt="<?php echo $video['title']; ?>" class="product-thumb">
                                </td>
                                <td>
                                    <strong><?php echo $video['title']; ?></strong>
                                </td>
                                <td><?php echo substr($video['description'], 0, 50) . '...'; ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $video['status']; ?>">
                                        <?php echo ucfirst($video['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($video['created_at'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-action edit" onclick="editVideo(<?php echo htmlspecialchars(json_encode($video)); ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn-action delete" onclick="deleteVideo(<?php echo $video['id']; ?>)">
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

    <!-- Video Modal -->
    <div id="videoModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Add Video</h3>
                <span class="close" onclick="closeVideoModal()">&times;</span>
            </div>
            <form id="videoForm" method="POST">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="id" id="videoId">
                
                <div class="form-group">
                    <label for="title">Video Title *</label>
                    <input type="text" id="title" name="title" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="video_path">Video URL *</label>
                    <input type="url" id="video_path" name="video_path" required placeholder="YouTube or Google Drive video URL">
                    <small style="color: #666; font-size: 0.8rem; display: block; margin-top: 5px;">
                        Supported: YouTube (youtube.com/watch?v=... or youtu.be/...) or Google Drive share links
                    </small>
                </div>
                
                <div class="form-group">
                    <label for="thumbnail">Thumbnail URL *</label>
                    <input type="url" id="thumbnail" name="thumbnail" required placeholder="https://example.com/thumbnail.jpg">
                    <small style="color: #666; font-size: 0.8rem; display: block; margin-top: 5px;">
                        Use a direct image URL for the video thumbnail
                    </small>
                </div>
                
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeVideoModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Video</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openVideoModal() {
            document.getElementById('modalTitle').textContent = 'Add Video';
            document.getElementById('formAction').value = 'add';
            document.getElementById('videoForm').reset();
            document.getElementById('videoModal').style.display = 'block';
        }

        function closeVideoModal() {
            document.getElementById('videoModal').style.display = 'none';
        }

        function editVideo(video) {
            document.getElementById('modalTitle').textContent = 'Edit Video';
            document.getElementById('formAction').value = 'edit';
            document.getElementById('videoId').value = video.id;
            document.getElementById('title').value = video.title;
            document.getElementById('description').value = video.description;
            document.getElementById('video_path').value = video.video_path;
            document.getElementById('thumbnail').value = video.thumbnail;
            document.getElementById('status').value = video.status;
            document.getElementById('videoModal').style.display = 'block';
        }

        function deleteVideo(id) {
            if (confirm('Are you sure you want to delete this video?')) {
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
            const modal = document.getElementById('videoModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>