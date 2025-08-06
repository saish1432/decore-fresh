<?php
session_start();
include 'config/database.php';

// --- NEW HELPER FUNCTION TO PROCESS VIDEO LINKS ---
// This function takes a URL from your database and checks if it's YouTube, Google Drive, or a direct link.
// It returns the correct URL needed to embed the video in an iframe.
function get_video_embed_details($url) {
    $details = ['type' => 'direct', 'url' => $url];

    // Check for YouTube
    if (preg_match('/(youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $matches)) {
        $video_id = $matches[2];
        $details['type'] = 'embed';
        $details['url'] = 'https://www.youtube.com/embed/' . $video_id;
    } 
    // Check for Google Drive
    elseif (preg_match('/drive\.google\.com\/file\/d\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
        $file_id = $matches[1];
        $details['type'] = 'embed';
        $details['url'] = 'https://drive.google.com/file/d/' . $file_id . '/preview';
    }

    return $details;
}

// Track visitor
$ip = $_SERVER['REMOTE_ADDR'];
$user_agent = $_SERVER['HTTP_USER_AGENT'];
$device_type = 'desktop';
if (preg_match('/Mobile|Android|iPhone|iPad/', $user_agent)) {
    $device_type = preg_match('/iPad/', $user_agent) ? 'tablet' : 'mobile';
}

$stmt = $pdo->prepare("INSERT INTO visitors (ip_address, device_type, visit_date) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE visit_count = visit_count + 1, last_visit = NOW()");
$stmt->execute([$ip, $device_type]);

// Get visitor count
$visitor_count = $pdo->query("SELECT SUM(visit_count) as total FROM visitors")->fetch()['total'] ?? 0;

// Get products
$products_stmt = $pdo->query("SELECT * FROM products WHERE status = 'active' ORDER BY created_at DESC");
$products = $products_stmt->fetchAll();

// Update product images with working Pexels URLs
$working_images = [
    'https://images.pexels.com/photos/170811/pexels-photo-170811.jpeg?auto=compress&cs=tinysrgb&w=400',
    'https://images.pexels.com/photos/116675/pexels-photo-116675.jpeg?auto=compress&cs=tinysrgb&w=400',
    'https://images.pexels.com/photos/35967/mini-cooper-auto-model-vehicle.jpg?auto=compress&cs=tinysrgb&w=400',
    'https://images.pexels.com/photos/244206/pexels-photo-244206.jpeg?auto=compress&cs=tinysrgb&w=400',
    'https://images.pexels.com/photos/305070/pexels-photo-305070.jpeg?auto=compress&cs=tinysrgb&w=400',
    'https://images.pexels.com/photos/1545743/pexels-photo-1545743.jpeg?auto=compress&cs=tinysrgb&w=400',
    'https://images.pexels.com/photos/919073/pexels-photo-919073.jpeg?auto=compress&cs=tinysrgb&w=400',
    'https://images.pexels.com/photos/1149137/pexels-photo-1149137.jpeg?auto=compress&cs=tinysrgb&w=400',
    'https://images.pexels.com/photos/1545743/pexels-photo-1545743.jpeg?auto=compress&cs=tinysrgb&w=400',
    'https://images.pexels.com/photos/3802510/pexels-photo-3802510.jpeg?auto=compress&cs=tinysrgb&w=400'
];

// Update category images with working URLs
$category_images = [
    'https://images.pexels.com/photos/170811/pexels-photo-170811.jpeg?auto=compress&cs=tinysrgb&w=100',
    'https://images.pexels.com/photos/116675/pexels-photo-116675.jpeg?auto=compress&cs=tinysrgb&w=100',
    'https://images.pexels.com/photos/35967/mini-cooper-auto-model-vehicle.jpg?auto=compress&cs=tinysrgb&w=100',
    'https://images.pexels.com/photos/244206/pexels-photo-244206.jpeg?auto=compress&cs=tinysrgb&w=100',
    'https://images.pexels.com/photos/305070/pexels-photo-305070.jpeg?auto=compress&cs=tinysrgb&w=100',
    'https://images.pexels.com/photos/1545743/pexels-photo-1545743.jpeg?auto=compress&cs=tinysrgb&w=100',
    'https://images.pexels.com/photos/919073/pexels-photo-919073.jpeg?auto=compress&cs=tinysrgb&w=100',
    'https://images.pexels.com/photos/1149137/pexels-photo-1149137.jpeg?auto=compress&cs=tinysrgb&w=100',
    'https://images.pexels.com/photos/3802510/pexels-photo-3802510.jpeg?auto=compress&cs=tinysrgb&w=100',
    'https://images.pexels.com/photos/170811/pexels-photo-170811.jpeg?auto=compress&cs=tinysrgb&w=100'
];

// Get categories
$categories_stmt = $pdo->query("SELECT * FROM categories WHERE status = 'active' ORDER BY name");
$categories = $categories_stmt->fetchAll();

// Get videos
$videos_stmt = $pdo->query("SELECT * FROM videos WHERE status = 'active' ORDER BY created_at DESC LIMIT 5");
$videos = $videos_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CAR DECORE - The Unique Car Accessories World</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- Header Section -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo-section">
                    <h1 class="company-name animated-text">CAR DECORE</h1>
                    <p class="tagline animated-tagline">The Unique Car Accessories World</p>
                </div>
                <div class="help-section">
                    <a href="https://wa.me/1234567890" class="help-btn animated-help" target="_blank">
                        <i class="fas fa-headset"></i>
                        <span>HELP</span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Fixed Cart Icon -->
    <div class="cart-icon" onclick="showCart()" id="cartIcon" style="display: none;">
        <i class="fas fa-shopping-cart"></i>
        <span class="cart-count" id="cartCount">0</span>
    </div>

    <!-- Auto-Scrolling Banner -->
    <section class="banner-section">
        <div class="banner-slider">
            <div class="slide active">
                <img src="https://images.pexels.com/photos/170811/pexels-photo-170811.jpeg?auto=compress&cs=tinysrgb&w=1200" alt="Car Accessories">
                <div class="slide-content">
                    <h2>Premium Car Accessories</h2>
                    <p>Transform your ride with our exclusive collection</p>
                </div>
            </div>
            <div class="slide">
                <img src="https://images.pexels.com/photos/116675/pexels-photo-116675.jpeg?auto=compress&cs=tinysrgb&w=1200" alt="Latest Technology">
                <div class="slide-content">
                    <h2>Latest Technology</h2>
                    <p>Experience cutting-edge automotive innovations</p>
                </div>
            </div>
            <div class="slide">
                <img src="https://images.pexels.com/photos/35967/mini-cooper-auto-model-vehicle.jpg?auto=compress&cs=tinysrgb&w=1200" alt="Quality Products">
                <div class="slide-content">
                    <h2>Quality Guaranteed</h2>
                    <p>100% authentic products with warranty</p>
                </div>
            </div>
        </div>
        <div class="banner-nav">
            <button class="prev-btn">&lt;</button>
            <button class="next-btn">&gt;</button>
        </div>
    </section>

    <!-- New Arrival Hot Products -->
    <section class="hot-products-section">
        <div class="container">
            <h2 class="section-title animated-title">🔥 New Arrival Hot Products - Top 10 List</h2>
            <div class="products-grid">
                <?php 
                $hot_products = array_slice($products, 0, 10);
                foreach($hot_products as $index => $product): 
                    $image_url = $working_images[$index % count($working_images)];
                ?>
                <div class="product-card">
                    <div class="product-image">
                        <img src="<?php echo htmlspecialchars($image_url); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="zoomable">
                        <div class="product-overlay">
                            <button class="zoom-btn"><i class="fas fa-search-plus"></i></button>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p><?php echo htmlspecialchars($product['description']); ?></p>
                        <div class="product-price">
                            <?php if($product['market_price'] > $product['price']): ?>
                                <span class="market-price">₹<?php echo htmlspecialchars($product['market_price']); ?></span>
                            <?php endif; ?>
                            <span class="current-price">₹<?php echo htmlspecialchars($product['price']); ?></span>
                        </div>
                        <div class="product-actions">
                            <button class="inquiry-btn" onclick="inquireProduct('<?php echo htmlspecialchars($product['name']); ?>')">
                                <i class="fab fa-whatsapp"></i> Inquiry Now
                            </button>
                            <button class="cart-btn" onclick="addToCart(<?php echo htmlspecialchars($product['id']); ?>, '<?php echo htmlspecialchars($product['name']); ?>', <?php echo htmlspecialchars($product['price']); ?>)">
                                <i class="fas fa-cart-plus"></i> Add to Cart
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Cart Summary -->
    <div class="cart-summary" id="cartSummary" style="display: none;">
        <div class="cart-content">
            <h3><i class="fas fa-shopping-cart"></i> Cart Summary</h3>
            <div id="cartItems"></div>
            <div class="cart-total">
                <p>Total Quantity: <span id="totalQty">0</span></p>
                <p>Total Value: ₹<span id="totalValue">0</span></p>
            </div>
            <button class="confirm-buy-btn" onclick="confirmPurchase()">
                <i class="fab fa-whatsapp"></i> Confirm to Buy
            </button>
            <button class="close-cart" onclick="closeCart()">×</button>
        </div>
    </div>

    <!-- Categories Section -->
    <section class="categories-section">
        <div class="container">
            <h2 class="section-title animated-title">🚗 Categories</h2>
            <div class="categories-grid">
                <?php foreach($categories as $index => $category): 
                    $cat_image_url = $category_images[$index % count($category_images)];
                ?>
                <div class="category-card animated-category">
                    <div class="category-image">
                        <img src="<?php echo htmlspecialchars($cat_image_url); ?>" alt="<?php echo htmlspecialchars($category['name']); ?>">
                    </div>
                    <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Car Accessories Section -->
    <section class="accessories-section">
        <div class="container">
            <h2 class="section-title bubble-title animated-title">🧼 Car Accessories</h2>
            <div class="accessories-grid">
                <?php 
                $accessories = array_filter($products, function($p) { return $p['category'] == 'accessories'; });
                $accessories = array_slice($accessories, 0, 15);
                foreach($accessories as $index => $accessory): 
                    $acc_image_url = $working_images[$index % count($working_images)];
                    $acc_image_url2 = $working_images[($index + 1) % count($working_images)];
                ?>
                <div class="accessory-card">
                    <div class="accessory-images">
                        <img src="<?php echo htmlspecialchars($acc_image_url); ?>" alt="<?php echo htmlspecialchars($accessory['name']); ?>" class="image-flip active">
                        <img src="<?php echo htmlspecialchars($acc_image_url2); ?>" alt="<?php echo htmlspecialchars($accessory['name']); ?>" class="image-flip">
                    </div>
                    <div class="accessory-info">
                        <h3><?php echo htmlspecialchars($accessory['name']); ?></h3>
                        <p><?php echo htmlspecialchars($accessory['description']); ?></p>
                        <div class="accessory-price">₹<?php echo htmlspecialchars($accessory['price']); ?></div>
                        <button class="buy-btn" onclick="buyNow(<?php echo htmlspecialchars($accessory['id']); ?>, '<?php echo htmlspecialchars($accessory['name']); ?>', <?php echo htmlspecialchars($accessory['price']); ?>)">
                            <i class="fab fa-whatsapp"></i> Buy Now
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Most Selling Products -->
    <section class="most-selling-section">
        <div class="container">
            <h2 class="section-title highlighted-title animated-title">🔥 MOST SELLING PRODUCTS</h2>
            <div class="most-selling-grid">
                <?php 
                $most_selling = array_filter($products, function($p) { return $p['is_most_selling'] == 1; });
                foreach($most_selling as $index => $product): 
                    $ms_image_url = $working_images[$index % count($working_images)];
                ?>
                <div class="selling-card">
                    <div class="selling-image">
                        <img src="<?php echo htmlspecialchars($ms_image_url); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="zoomable">
                    </div>
                    <div class="selling-info">
                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p><?php echo htmlspecialchars($product['description']); ?></p>
                        <div class="price-box">
                            <?php if($product['market_price'] > $product['price']): ?>
                            <div class="market-price-box">
                                <span>Market Price</span>
                                <span>₹<?php echo htmlspecialchars($product['market_price']); ?></span>
                            </div>
                            <?php endif; ?>
                            <div class="discounted-price-box">
                                <span>Our Price</span>
                                <span>₹<?php echo htmlspecialchars($product['price']); ?></span>
                            </div>
                        </div>
                        <button class="buy-now-btn" onclick="addToCartAndShow(<?php echo htmlspecialchars($product['id']); ?>, '<?php echo htmlspecialchars($product['name']); ?>', <?php echo htmlspecialchars($product['price']); ?>)">
                            <i class="fas fa-cart-plus"></i> Buy NOW
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ==== MODIFIED VIDEO SECTION ==== -->
    <section class="video-section">
        <div class="container">
            <h2 class="section-title animated-title">🎥 Product Demo Videos</h2>
            <div class="videos-grid">
                <!-- Hardcoded Sample Videos (kept as is) -->
                <div class="video-card">
                    <div class="video-thumbnail">
                        <video poster="https://images.pexels.com/photos/170811/pexels-photo-170811.jpeg?auto=compress&cs=tinysrgb&w=400">
                            <source src="https://sample-videos.com/zip/10/mp4/SampleVideo_1280x720_1mb.mp4" type="video/mp4">
                        </video>
                        <div class="play-overlay">
                            <button class="play-btn" onclick="playVideo(this)">
                                <i class="fas fa-play"></i>
                            </button>
                        </div>
                    </div>
                    <div class="video-info">
                        <h4>Dashboard Camera Installation Guide</h4>
                        <p>Complete step-by-step installation process for dashboard cameras</p>
                    </div>
                </div>
                <!-- Add other hardcoded sample videos here if you have them -->

                <!-- DYNAMIC VIDEOS FROM DATABASE (YouTube, Google Drive, or Direct Link) -->
                <?php if(!empty($videos)): ?>
                    <?php foreach($videos as $video): 
                        // Use the helper function to get video details
                        $video_details = get_video_embed_details($video['video_path']);
                    ?>
                    <div class="video-card">
                        <div class="video-thumbnail">
                            <?php if ($video_details['type'] === 'embed'): ?>
                                <!-- This is for YouTube or Google Drive -->
                                <img src="<?php echo htmlspecialchars($video['thumbnail']); ?>" alt="<?php echo htmlspecialchars($video['title']); ?>" style="width:100%; height:100%; object-fit:cover;">
                                <div class="play-overlay">
                                    <button class="play-btn" onclick="playEmbedVideo(this)" data-embed-src="<?php echo htmlspecialchars($video_details['url']); ?>">
                                        <i class="fas fa-play"></i>
                                    </button>
                                </div>
                            <?php else: ?>
                                <!-- This is for direct .mp4 links -->
                                <video poster="<?php echo htmlspecialchars($video['thumbnail']); ?>" preload="none">
                                    <source src="<?php echo htmlspecialchars($video_details['url']); ?>" type="video/mp4">
                                </video>
                                <div class="play-overlay">
                                    <button class="play-btn" onclick="playVideo(this)">
                                        <i class="fas fa-play"></i>
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="video-info">
                            <h4><?php echo htmlspecialchars($video['title']); ?></h4>
                            <p><?php echo htmlspecialchars($video['description']); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- About Us Section -->
    <section class="about-section">
        <div class="container">
            <h2 class="section-title animated-title">About Us</h2>
            <div class="about-content">
                <div class="about-points">
                    <div class="about-point">
                        <i class="fas fa-car"></i>
                        <span>We have various brands accessories</span>
                    </div>
                    <div class="about-point">
                        <i class="fas fa-microchip"></i>
                        <span>Latest technology gadgets for your car</span>
                    </div>
                    <div class="about-point">
                        <i class="fas fa-tags"></i>
                        <span>Get discount offer prices order online</span>
                    </div>
                    <div class="about-point">
                        <i class="fas fa-shipping-fast"></i>
                        <span>Fast service, your satisfaction</span>
                    </div>
                    <div class="about-point">
                        <i class="fas fa-award"></i>
                        <span>We have more than 10 years experience in car accessories</span>
                    </div>
                    <div class="about-point">
                        <i class="fas fa-handshake"></i>
                        <span>Your Trust, Our Commitment</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="office-address">
                    <h3>Office Address</h3>
                    <div class="address-box">
                        <i class="fas fa-map-marker-alt"></i>
                        <div>
                            <p>CAR DECORE Store</p>
                            <p>123 Auto Parts Street</p>
                            <p>Car Accessories Hub</p>
                            <p>City - 400001</p>
                            <p>Phone: +91 1234567890</p>
                        </div>
                    </div>
                </div>
                <div class="visitor-counter">
                    <h3>Website Visitors</h3>
                    <div class="counter-box">
                        <i class="fas fa-users"></i>
                        <span class="count"><?php echo number_format($visitor_count); ?></span>
                        <span class="label">Total Visits</span>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 All Rights Reserved. Developed & Managed by GTAi.in</p>
            </div>
        </div>
    </footer>

    <!-- Image Modal -->
    <div id="imageModal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <img id="modalImage" src="" alt="">
        </div>
    </div>

    <script src="assets/js/script.js"></script>

    <!-- ==== NEW JAVASCRIPT FOR VIDEOS ==== -->
    <script>
        /**
         * This function handles playing embedded videos from YouTube/Google Drive.
         * It replaces the thumbnail with an iframe player on click.
         */
        function playEmbedVideo(button) {
            const embedUrl = button.getAttribute('data-embed-src');
            if (!embedUrl) return;

            const thumbnailContainer = button.closest('.video-thumbnail');
            if (!thumbnailContainer) return;

            const iframe = document.createElement('iframe');
            // Add autoplay=1 to the URL to start the video immediately (works for YouTube)
            iframe.setAttribute('src', embedUrl + '?autoplay=1');
            iframe.setAttribute('frameborder', '0');
            iframe.setAttribute('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture');
            iframe.setAttribute('allowfullscreen', '');
            iframe.style.width = '100%';
            iframe.style.height = '100%';
            iframe.style.position = 'absolute';
            iframe.style.top = '0';
            iframe.style.left = '0';

            // Replace the container's content (img and overlay) with the iframe
            thumbnailContainer.innerHTML = '';
            thumbnailContainer.appendChild(iframe);
        }

        /**
         * This function handles playing direct .mp4 video files.
         * It's used for your hardcoded sample videos.
         */
        function playVideo(button) {
            const thumbnailContainer = button.closest('.video-thumbnail');
            const video = thumbnailContainer.querySelector('video');
            if (video) {
                // Hide the overlay and play the video
                const overlay = button.closest('.play-overlay');
                if (overlay) {
                    overlay.style.display = 'none';
                }
                video.play();
                video.setAttribute('controls', 'true'); // Show native controls after play
            }
        }
    </script>
</body>
</html>