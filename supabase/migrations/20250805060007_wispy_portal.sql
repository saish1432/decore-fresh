-- CAR DECORE Database Structure
-- Compatible with phpMyAdmin and Hostinger

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Database: car_decore

-- --------------------------------------------------------

-- Table structure for table `admin_users`
CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `admin_users`
INSERT INTO `admin_users` (`username`, `password`, `email`) VALUES
('admin', '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8', 'admin@cardecore.com');

-- --------------------------------------------------------

-- Table structure for table `categories`
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `categories`
INSERT INTO `categories` (`name`, `slug`, `logo`, `description`, `status`) VALUES
('MARUTI', 'maruti', 'https://images.pexels.com/photos/3802510/pexels-photo-3802510.jpeg?auto=compress&cs=tinysrgb&w=100', 'Maruti Suzuki car accessories', 'active'),
('HONDA', 'honda', 'https://images.pexels.com/photos/1545743/pexels-photo-1545743.jpeg?auto=compress&cs=tinysrgb&w=100', 'Honda car accessories', 'active'),
('SKODA', 'skoda', 'https://images.pexels.com/photos/3807277/pexels-photo-3807277.jpeg?auto=compress&cs=tinysrgb&w=100', 'Skoda car accessories', 'active'),
('AUDI', 'audi', 'https://images.pexels.com/photos/2127733/pexels-photo-2127733.jpeg?auto=compress&cs=tinysrgb&w=100', 'Audi luxury car accessories', 'active'),
('TATA', 'tata', 'https://images.pexels.com/photos/3964704/pexels-photo-3964704.jpeg?auto=compress&cs=tinysrgb&w=100', 'Tata Motors accessories', 'active'),
('HYUNDAI', 'hyundai', 'https://images.pexels.com/photos/2365572/pexels-photo-2365572.jpeg?auto=compress&cs=tinysrgb&w=100', 'Hyundai car accessories', 'active'),
('BMW', 'bmw', 'https://images.pexels.com/photos/2365573/pexels-photo-2365573.jpeg?auto=compress&cs=tinysrgb&w=100', 'BMW luxury accessories', 'active'),
('KIA', 'kia', 'https://images.pexels.com/photos/2365574/pexels-photo-2365574.jpeg?auto=compress&cs=tinysrgb&w=100', 'KIA car accessories', 'active'),
('MG', 'mg', 'https://images.pexels.com/photos/2365575/pexels-photo-2365575.jpeg?auto=compress&cs=tinysrgb&w=100', 'MG Motor accessories', 'active'),
('TOYOTA', 'toyota', 'https://images.pexels.com/photos/2365576/pexels-photo-2365576.jpeg?auto=compress&cs=tinysrgb&w=100', 'Toyota car accessories', 'active');

-- --------------------------------------------------------

-- Table structure for table `products`
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `market_price` decimal(10,2) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `image1` varchar(255) DEFAULT NULL,
  `image2` varchar(255) DEFAULT NULL,
  `is_hot` tinyint(1) NOT NULL DEFAULT '0',
  `is_new` tinyint(1) NOT NULL DEFAULT '0',
  `is_most_selling` tinyint(1) NOT NULL DEFAULT '0',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `category` (`category`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `products`
INSERT INTO `products` (`name`, `slug`, `description`, `price`, `market_price`, `category`, `image1`, `image2`, `is_hot`, `is_new`, `is_most_selling`, `status`) VALUES
('Premium Car Dashboard Camera', 'premium-car-dashboard-camera', 'High-quality dashboard camera with night vision and GPS tracking', 2499.00, 3499.00, 'accessories', 'https://images.pexels.com/photos/3802510/pexels-photo-3802510.jpeg', 'https://images.pexels.com/photos/1545743/pexels-photo-1545743.jpeg', 1, 1, 0, 'active'),
('LED Headlight Bulbs', 'led-headlight-bulbs', 'Bright LED headlight bulbs with long lifespan', 899.00, 1299.00, 'accessories', 'https://images.pexels.com/photos/3807277/pexels-photo-3807277.jpeg', 'https://images.pexels.com/photos/2127733/pexels-photo-2127733.jpeg', 1, 0, 1, 'active'),
('Car Phone Holder', 'car-phone-holder', 'Adjustable phone holder for dashboard mounting', 299.00, 499.00, 'accessories', 'https://images.pexels.com/photos/3964704/pexels-photo-3964704.jpeg', 'https://images.pexels.com/photos/2365572/pexels-photo-2365572.jpeg', 0, 1, 1, 'active'),
('Wireless Car Charger', 'wireless-car-charger', 'Fast wireless charging pad for your car', 1299.00, 1799.00, 'accessories', 'https://images.pexels.com/photos/2365573/pexels-photo-2365573.jpeg', 'https://images.pexels.com/photos/2365574/pexels-photo-2365574.jpeg', 1, 1, 0, 'active'),
('Car Air Purifier', 'car-air-purifier', 'Compact air purifier with HEPA filter for clean air', 1599.00, 2199.00, 'accessories', 'https://images.pexels.com/photos/2365575/pexels-photo-2365575.jpeg', 'https://images.pexels.com/photos/2365576/pexels-photo-2365576.jpeg', 0, 1, 1, 'active'),
('Seat Covers Premium Set', 'seat-covers-premium-set', 'Premium leather seat covers for all car models', 3499.00, 4999.00, 'MARUTI', 'https://images.pexels.com/photos/3802510/pexels-photo-3802510.jpeg', 'https://images.pexels.com/photos/1545743/pexels-photo-1545743.jpeg', 1, 0, 1, 'active'),
('Floor Mats Rubber Set', 'floor-mats-rubber-set', 'Durable rubber floor mats for Honda models', 799.00, 1199.00, 'HONDA', 'https://images.pexels.com/photos/3807277/pexels-photo-3807277.jpeg', 'https://images.pexels.com/photos/2127733/pexels-photo-2127733.jpeg', 0, 1, 0, 'active'),
('Car Perfume Dispenser', 'car-perfume-dispenser', 'Automatic perfume dispenser with multiple scents', 699.00, 999.00, 'accessories', 'https://images.pexels.com/photos/3964704/pexels-photo-3964704.jpeg', 'https://images.pexels.com/photos/2365572/pexels-photo-2365572.jpeg', 1, 1, 1, 'active'),
('Backup Camera System', 'backup-camera-system', 'HD backup camera system with LCD display', 4499.00, 6999.00, 'accessories', 'https://images.pexels.com/photos/2365573/pexels-photo-2365573.jpeg', 'https://images.pexels.com/photos/2365574/pexels-photo-2365574.jpeg', 1, 0, 1, 'active'),
('Steering Wheel Cover', 'steering-wheel-cover', 'Comfortable grip steering wheel cover', 399.00, 699.00, 'accessories', 'https://images.pexels.com/photos/2365575/pexels-photo-2365575.jpeg', 'https://images.pexels.com/photos/2365576/pexels-photo-2365576.jpeg', 0, 1, 0, 'active');

-- --------------------------------------------------------

-- Table structure for table `orders`
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_items` text NOT NULL,
  `items_count` int(11) NOT NULL DEFAULT '0',
  `total_amount` decimal(10,2) NOT NULL,
  `customer_phone` varchar(20) DEFAULT NULL,
  `status` enum('pending','completed','cancelled') NOT NULL DEFAULT 'pending',
  `order_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_date` (`order_date`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Table structure for table `videos`
CREATE TABLE `videos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `video_path` varchar(255) NOT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `videos`
INSERT INTO `videos` (`title`, `description`, `video_path`, `thumbnail`, `status`) VALUES
('Dashboard Camera Installation', 'Step by step guide to install dashboard camera', 'uploads/videos/dashboard-install.mp4', 'uploads/thumbnails/dashboard-thumb.jpg', 'active'),
('LED Headlight Upgrade', 'How to upgrade to LED headlights', 'uploads/videos/led-upgrade.mp4', 'uploads/thumbnails/led-thumb.jpg', 'active'),
('Car Air Purifier Setup', 'Installing and configuring car air purifier', 'uploads/videos/air-purifier.mp4', 'uploads/thumbnails/purifier-thumb.jpg', 'active');

-- --------------------------------------------------------

-- Table structure for table `visitors`
CREATE TABLE `visitors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `device_type` enum('mobile','tablet','desktop') NOT NULL DEFAULT 'desktop',
  `visit_count` int(11) NOT NULL DEFAULT '1',
  `first_visit` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_visit` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `visit_date` date NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ip_date` (`ip_address`,`visit_date`),
  KEY `device_type` (`device_type`),
  KEY `visit_date` (`visit_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Table structure for table `settings`
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `settings`
INSERT INTO `settings` (`setting_key`, `setting_value`, `description`) VALUES
('whatsapp_number', '1234567890', 'WhatsApp number for customer inquiries'),
('site_title', 'CAR DECORE - The Unique Car Accessories World', 'Website title'),
('office_address', '123 Auto Parts Street, Car Accessories Hub, City - 400001', 'Office address for contact'),
('admin_email', 'admin@cardecore.com', 'Admin email address'),
('currency_symbol', '₹', 'Currency symbol for prices'),
('timezone', 'Asia/Kolkata', 'Website timezone');

COMMIT;