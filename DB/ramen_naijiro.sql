-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 10, 2025 at 05:21 PM
-- Server version: 8.0.44
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ramen_naijiro`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `admin_id` int UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `branch_id` varchar(20) NOT NULL,
  `branch_name` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`admin_id`, `name`, `email`, `branch_id`, `branch_name`, `password`, `created_at`) VALUES
(1, 'General Trias Admin', 'admin.gentrias@naijiro.test', 'gen-trias', 'Ramen Naijiro - General Trias', 'Pa$$word1', '2025-12-10 12:40:39'),
(2, 'Dasmari?as Admin', 'admin.dasma@naijiro.test', 'dasma', 'Ramen Naijiro - Dasmari?as', 'Pa$$word1', '2025-12-10 12:40:39'),
(3, 'Odasiba Admin', 'admin.odasiba@naijiro.test', 'odasiba', 'Ramen Naijiro - Odasiba', 'Pa$$word1', '2025-12-10 12:40:39'),
(4, 'Marikina Admin', 'admin.marikina@naijiro.test', 'marikina', 'Ramen Naijiro - Marikina', 'Pa$$word1', '2025-12-10 12:40:39'),
(5, 'Cainta Admin', 'admin.cainta@naijiro.test', 'cainta', 'Ramen Naijiro - Cainta', 'Pa$$word1', '2025-12-10 12:40:39');

-- --------------------------------------------------------

--
-- Table structure for table `menu_categories`
--

CREATE TABLE `menu_categories` (
  `category_id` int UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `menu_categories`
--

INSERT INTO `menu_categories` (`category_id`, `name`, `slug`) VALUES
(1, 'Ramen', 'ramen'),
(2, 'Sides', 'sides'),
(3, 'Ramen Extras', 'extras');

-- --------------------------------------------------------

--
-- Table structure for table `menu_items`
--

CREATE TABLE `menu_items` (
  `item_id` int UNSIGNED NOT NULL,
  `category_id` int UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text,
  `image_path` varchar(255) DEFAULT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `menu_items`
--

INSERT INTO `menu_items` (`item_id`, `category_id`, `name`, `description`, `image_path`, `is_available`, `created_at`, `updated_at`) VALUES
(1, 1, 'Shoyu Ramen', 'Soy sauce - chashu pork - beansprouts - leeks - egg', '../img/menu img/shoyu3.jpg', 1, '2025-12-09 08:55:30', '2025-12-09 08:55:30'),
(2, 1, 'Tonkotsu Ramen', 'chashu pork - black fungus - spring onions - leeks - egg', '../img/menu img/tonkotsu3.jpg', 1, '2025-12-09 08:55:30', '2025-12-09 08:55:30'),
(3, 1, 'Miso Ramen', 'miso - chashu pork - wakame seaweed - spring onion - egg', '../img/menu img/miso2.jpg', 1, '2025-12-09 08:55:30', '2025-12-09 08:55:30'),
(4, 1, 'Tantanmen Ramen', 'spicy - chashu pork - beansprouts - seaweed strips - sesame seeds - egg', '../img/menu img/tantanmen.jpg', 1, '2025-12-09 08:55:30', '2025-12-09 08:55:30'),
(5, 1, 'Chicken Butter Ramen', 'chicken fillet - butter - seaweed strips - spring onion - egg', '../img/menu img/chicken butter.jpg', 1, '2025-12-09 08:55:30', '2025-12-09 08:55:30'),
(6, 1, 'Black Garlic Ramen', 'black garlic - chashu pork - wakame - seaweed - kikurage - egg', '../img/menu img/black garlic2.jpg', 1, '2025-12-09 08:55:30', '2025-12-09 08:55:30'),
(7, 1, 'Red Ramen', 'spicy meatball - kikurage - spring onion - seaweed strips - chashu pork', '../img/menu img/red ramen.jpg', 1, '2025-12-09 08:55:30', '2025-12-09 08:55:30'),
(8, 2, 'Gyoza', 'Pan-fried dumplings', '../img/menu img/gyoza.jpg', 1, '2025-12-09 08:55:42', '2025-12-09 08:55:42'),
(9, 2, 'Chicken Karaage', 'Crispy Japanese fried chicken', '../img/menu img/kaarage.jpg', 1, '2025-12-09 08:55:42', '2025-12-09 08:55:42'),
(10, 2, 'Teriyaki Tofu', 'Grilled tofu with teriyaki sauce', '../img/menu img/teriyaki tofu.jpg', 1, '2025-12-09 08:55:42', '2025-12-09 08:55:42'),
(11, 2, 'Garlic Butter Tofu', 'Grilled tofu with garlic butter sauce', '../img/menu img/kaarage.jpg', 1, '2025-12-09 08:55:42', '2025-12-09 08:55:42'),
(12, 3, 'Beansprouts', NULL, NULL, 1, '2025-12-09 08:55:46', '2025-12-09 08:55:46'),
(13, 3, 'Chashu Pork', NULL, NULL, 1, '2025-12-09 08:55:46', '2025-12-09 08:55:46'),
(14, 3, 'Red Spicy Meatball', NULL, NULL, 1, '2025-12-09 08:55:46', '2025-12-09 08:55:46'),
(15, 3, 'Kikurage', NULL, NULL, 1, '2025-12-09 08:55:46', '2025-12-09 08:55:46'),
(16, 3, 'Wakame Seaweed', NULL, NULL, 1, '2025-12-09 08:55:46', '2025-12-09 08:55:46'),
(17, 3, 'Salted Seaweed Strips', NULL, NULL, 1, '2025-12-09 08:55:46', '2025-12-09 08:55:46'),
(18, 3, 'Egg', NULL, NULL, 1, '2025-12-09 08:55:46', '2025-12-09 08:55:46'),
(19, 3, 'Noodles', NULL, NULL, 1, '2025-12-09 08:55:46', '2025-12-09 08:55:46'),
(20, 3, 'Broth Refill', NULL, NULL, 1, '2025-12-09 08:55:46', '2025-12-09 08:55:46');

-- --------------------------------------------------------

--
-- Table structure for table `menu_item_sizes`
--

CREATE TABLE `menu_item_sizes` (
  `size_id` int UNSIGNED NOT NULL,
  `item_id` int UNSIGNED NOT NULL,
  `size_code` varchar(10) NOT NULL,
  `size_label` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `menu_item_sizes`
--

INSERT INTO `menu_item_sizes` (`size_id`, `item_id`, `size_code`, `size_label`, `price`, `is_available`) VALUES
(1, 1, 'S', 'Small', 169.00, 1),
(2, 1, 'R', 'Regular', 199.00, 1),
(3, 1, 'L', 'Large', 229.00, 1),
(4, 2, 'S', 'Small', 159.00, 1),
(5, 2, 'R', 'Regular', 189.00, 1),
(6, 2, 'L', 'Large', 219.00, 1),
(7, 3, 'S', 'Small', 169.00, 1),
(8, 3, 'R', 'Regular', 199.00, 1),
(9, 3, 'L', 'Large', 229.00, 1),
(10, 4, 'S', 'Small', 169.00, 1),
(11, 4, 'R', 'Regular', 199.00, 1),
(12, 4, 'L', 'Large', 229.00, 1),
(13, 5, 'S', 'Small', 189.00, 1),
(14, 5, 'L', 'Large', 279.00, 1),
(15, 6, 'S', 'Small', 169.00, 1),
(16, 6, 'R', 'Regular', 199.00, 1),
(17, 6, 'L', 'Large', 229.00, 1),
(18, 7, 'R', 'Regular', 289.00, 1),
(19, 7, 'L', 'Large', 319.00, 1),
(20, 8, '4PCS', '4 pcs', 89.00, 1),
(21, 8, '6PCS', '6 pcs', 129.00, 1),
(22, 9, 'REG', 'Regular', 149.00, 1),
(23, 10, 'REG', 'Regular', 89.00, 1),
(24, 11, 'REG', 'Regular', 89.00, 1),
(25, 12, 'ADD', 'Add-on', 20.00, 1),
(26, 13, 'ADD', 'Add-on', 60.00, 1),
(27, 14, 'ADD', 'Add-on', 60.00, 1),
(28, 15, 'ADD', 'Add-on', 20.00, 1),
(29, 16, 'ADD', 'Add-on', 20.00, 1),
(30, 17, 'ADD', 'Add-on', 20.00, 1),
(31, 18, 'ADD', 'Add-on', 20.00, 1),
(32, 19, 'ADD', 'Add-on', 40.00, 1),
(33, 20, 'R', 'Regular Bowl', 80.00, 1),
(34, 20, 'L', 'Large Bowl', 100.00, 1);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int UNSIGNED NOT NULL,
  `order_code` varchar(20) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_phone` varchar(50) NOT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `branch_id` varchar(20) DEFAULT NULL,
  `branch_name` varchar(100) NOT NULL,
  `province` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `barangay` varchar(100) DEFAULT NULL,
  `address` text,
  `landmark` varchar(255) DEFAULT NULL,
  `order_type` enum('Delivery','Pickup') NOT NULL DEFAULT 'Delivery',
  `payment_method` enum('cod','paymongo') NOT NULL DEFAULT 'cod',
  `subtotal` decimal(10,2) NOT NULL DEFAULT '0.00',
  `delivery_fee` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `status` enum('pending','preparing','out_for_delivery','completed','cancelled') NOT NULL DEFAULT 'pending',
  `cart_json` longtext,
  `customer_lat` decimal(10,7) DEFAULT NULL,
  `customer_lon` decimal(10,7) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `order_code`, `customer_name`, `customer_phone`, `customer_email`, `branch_id`, `branch_name`, `province`, `city`, `barangay`, `address`, `landmark`, `order_type`, `payment_method`, `subtotal`, `delivery_fee`, `total_amount`, `status`, `cart_json`, `customer_lat`, `customer_lon`, `created_at`, `updated_at`) VALUES
(1, 'RN-000001', 'adasda', '09293811134', 'weqwqeq@gmail.com', 'gen-trias', 'Ramen Naijiro - General Trias', 'Cavite', 'General Trias', 'Manggahan', 'San Marino Classic\r\nBlk 25 Lot 13', '', 'Pickup', 'paymongo', 159.00, 0.00, 159.00, 'completed', '{\"items\":[{\"name\":\"Tonkotsu Ramen\",\"size\":\"S\",\"extras\":\"None (₱0)\",\"qty\":1,\"line_total\":159}],\"total\":159,\"orderType\":\"Pickup\",\"paymentMethod\":\"GCash\",\"location\":{\"province\":\"Cavite\",\"city\":\"General Trias\",\"barangay\":\"Manggahan\",\"branchId\":\"gen-trias\",\"branchName\":\"Ramen Naijiro - General Trias\",\"deliveryAllowed\":\"1\"},\"createdAt\":\"2025-12-09T14:45:45.600Z\"}', NULL, NULL, '2025-12-09 22:57:03', '2025-12-10 22:39:19'),
(2, 'RN-000002', 'adasda', '09293811134', 'weqwqeq@gmail.com', 'gen-trias', 'Ramen Naijiro - General Trias', 'Cavite', 'General Trias', 'Manggahan', 'San Marino Classic\r\nBlk 25 Lot 13', '', 'Pickup', 'paymongo', 159.00, 0.00, 159.00, 'pending', '{\"items\":[{\"name\":\"Tonkotsu Ramen\",\"size\":\"S\",\"extras\":\"None (₱0)\",\"qty\":1,\"line_total\":159}],\"total\":159,\"orderType\":\"Pickup\",\"paymentMethod\":\"GCash\",\"location\":{\"province\":\"Cavite\",\"city\":\"General Trias\",\"barangay\":\"Manggahan\",\"branchId\":\"gen-trias\",\"branchName\":\"Ramen Naijiro - General Trias\",\"deliveryAllowed\":\"1\"},\"createdAt\":\"2025-12-10T16:18:34.400Z\"}', NULL, NULL, '2025-12-11 00:18:56', '2025-12-11 00:18:56');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `item_id` int UNSIGNED NOT NULL,
  `order_id` int UNSIGNED NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `size` varchar(50) DEFAULT NULL,
  `extras` varchar(255) DEFAULT NULL,
  `quantity` int UNSIGNED NOT NULL DEFAULT '1',
  `line_price` decimal(10,2) NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `res_id` int UNSIGNED NOT NULL,
  `res_code` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `branch` varchar(100) NOT NULL,
  `pax` int UNSIGNED NOT NULL DEFAULT '1',
  `date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `menu_categories`
--
ALTER TABLE `menu_categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `fk_menu_category` (`category_id`);

--
-- Indexes for table `menu_item_sizes`
--
ALTER TABLE `menu_item_sizes`
  ADD PRIMARY KEY (`size_id`),
  ADD KEY `fk_menu_item` (`item_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `idx_order_code` (`order_code`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `idx_order_items_order_id` (`order_id`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`res_id`),
  ADD UNIQUE KEY `res_code` (`res_code`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `admin_id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `menu_categories`
--
ALTER TABLE `menu_categories`
  MODIFY `category_id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `item_id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `menu_item_sizes`
--
ALTER TABLE `menu_item_sizes`
  MODIFY `size_id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `item_id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `res_id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD CONSTRAINT `fk_menu_category` FOREIGN KEY (`category_id`) REFERENCES `menu_categories` (`category_id`) ON DELETE CASCADE;

--
-- Constraints for table `menu_item_sizes`
--
ALTER TABLE `menu_item_sizes`
  ADD CONSTRAINT `fk_menu_item` FOREIGN KEY (`item_id`) REFERENCES `menu_items` (`item_id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
