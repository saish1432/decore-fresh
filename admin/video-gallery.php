<?php
// Assume videos are stored in a flat array or loaded from JSON (you can replace with include or DB code)
$videos = [
  [
    'title' => 'Scorpio Demo',
    'description' => 'New Launched...',
    'url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
    'status' => 'Active'
  ],
  [
    'title' => 'My Car',
    'description' => 'New Arrival...',
    'url' => 'https://www.youtube.com/watch?v=3JZ_D3ELwOQ',
    'status' => 'Active'
  ],
  [
    'title' => 'New Gadget in the Market',
    'description' => 'Tap To Share...',
    'url' => 'https://drive.google.com/file/d/1ABCD12345EFG678/view?usp=sharing',
    'status' => 'Active'
  ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Product Demo Videos</title>
  <link rel="stylesheet" href="style.css" />
  <style>
    .video-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
      gap: 20px;
      margin-top: 2rem;
    }

    .video-card {
      background: linear-gradient(to right, #8e2de2, #4a00e0);
      border-radius: 15px;
      padding: 1rem;
      color: white;
      box-shadow: 0 8px 24px rgba(0,0,0,0.15);
      transition: transform 0.3s ease;
    }

    .video-card:hover {
      transform: scale(1.03);
    }

    iframe {
      width: 100%;
      height: 200px;
      border: none;
      border-radius: 10px;
      margin-top: 10px;
    }

    .title {
      font-weight: bold;
      font-size: 1.2rem;
    }

    .subtitle {
      font-size: 0.9rem;
      opacity: 0.8;
    }

    h2 {
      text-align: center;
      margin-bottom: 1rem;
    }
  </style>
</head>
<body>
  <div id="app">
    <h2>📽️ Product Demo Videos</h2>
    <div class="video-grid">
      <?php foreach ($videos as $video): ?>
        <?php
          if ($video['status'] != 'Active') continue;

          $url = $video['url'];
          $embedUrl = '';

          // Detect YouTube
          if (strpos($url, 'youtube.com') !== false || strpos($url, 'youtu.be') !== false) {
              if (strpos($url, 'watch?v=') !== false) {
                  parse_str(parse_url($url, PHP_URL_QUERY), $ytParams);
                  $videoId = $ytParams['v'] ?? '';
              } elseif (strpos($url, 'youtu.be/') !== false) {
                  $videoId = basename(parse_url($url, PHP_URL_PATH));
              }
              $embedUrl = "https://www.youtube.com/embed/" . $videoId;
          }

          // Detect Google Drive
          elseif (strpos($url, 'drive.google.com') !== false) {
              if (preg_match('/\/d\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
                  $fileId = $matches[1];
                  $embedUrl = "https://drive.google.com/file/d/$fileId/preview";
              }
          }
        ?>
        <div class="video-card">
          <div class="title"><?= htmlspecialchars($video['title']) ?></div>
          <div class="subtitle"><?= htmlspecialchars($video['description']) ?></div>
          <?php if ($embedUrl): ?>
            <iframe src="<?= htmlspecialchars($embedUrl) ?>" allowfullscreen></iframe>
          <?php else: ?>
            <p style="color: yellow;">Invalid or unsupported video URL.</p>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</body>
</html>
