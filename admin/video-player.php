<?php
include '../config/database.php';

$videos = $pdo->query("SELECT * FROM videos WHERE status='active' ORDER BY created_at DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Videos - CAR DECORE</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .video-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .video-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .video-card:hover {
            transform: translateY(-5px);
        }
        .video-player-container {
            position: relative;
            padding-bottom: 56.25%; /* 16:9 aspect ratio */
            height: 0;
            overflow: hidden;
        }
        .video-player-container iframe,
        .video-player-container video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: none;
        }
        .video-info {
            padding: 15px;
        }
        .video-title {
            font-weight: bold;
            font-size: 18px;
            margin-bottom: 8px;
            color: #333;
        }
        .video-description {
            color: #666;
            font-size: 14px;
            line-height: 1.5;
        }
        .page-header {
            text-align: center;
            padding: 30px 0;
            background: #f8f9fa;
            margin-bottom: 30px;
        }
        .page-header h1 {
            color: #333;
            margin-bottom: 10px;
        }
        .page-header p {
            color: #666;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="page-header">
        <h1>Product Installation Videos</h1>
        <p>Step-by-step guides for our products</p>
    </div>

    <div class="video-gallery">
        <?php foreach($videos as $video): ?>
        <div class="video-card">
            <div class="video-player-container">
                <?php if (strpos($video['video_path'], 'youtube.com') !== false || strpos($video['video_path'], 'youtu.be') !== false): ?>
                    <iframe src="<?php echo $video['video_path']; ?>?rel=0&modestbranding=1" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                <?php elseif (strpos($video['video_path'], 'drive.google.com') !== false): ?>
                    <?php
                    $drive_id = '';
                    if (preg_match('/\/file\/d\/([^\/]+)/', $video['video_path'], $matches)) {
                        $drive_id = $matches[1];
                    }
                    ?>
                    <iframe src="https://drive.google.com/file/d/<?php echo $drive_id; ?>/preview" allowfullscreen></iframe>
                <?php else: ?>
                    <video controls>
                        <source src="<?php echo $video['video_path']; ?>" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                <?php endif; ?>
            </div>
            <div class="video-info">
                <h3 class="video-title"><?php echo htmlspecialchars($video['title']); ?></h3>
                <p class="video-description"><?php echo htmlspecialchars($video['description']); ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>