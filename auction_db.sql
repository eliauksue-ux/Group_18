-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Nov 13, 2025 at 04:40 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `auction_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `Auction`
--

CREATE TABLE `Auction` (
  `auction_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `winner_id` int(11) DEFAULT NULL,
  `final_price` decimal(10,2) DEFAULT NULL,
  `current_price` decimal(10,2) DEFAULT 0.00,
  `status` enum('ongoing','completed','cancelled') DEFAULT 'ongoing'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Auction`
--

INSERT INTO `Auction` (`auction_id`, `item_id`, `start_date`, `end_date`, `winner_id`, `final_price`, `current_price`, `status`) VALUES
(1, 1, '2025-11-12 14:25:25', '2025-11-13 15:25:25', NULL, NULL, 290.00, 'ongoing'),
(2, 4, '2025-11-09 15:25:25', '2025-11-12 14:25:25', 1, 255.00, 255.00, 'completed'),
(3, 1, '2025-11-13 11:50:49', '2025-11-14 12:50:49', NULL, NULL, 290.00, 'ongoing'),
(4, 4, '2025-11-10 12:50:49', '2025-11-13 11:50:49', 1, 255.00, 255.00, 'completed'),
(5, 27, '2025-11-13 16:29:00', '2025-11-14 16:29:00', NULL, NULL, 310.00, 'ongoing');

-- --------------------------------------------------------

--
-- Table structure for table `Bid`
--

CREATE TABLE `Bid` (
  `bid_id` int(11) NOT NULL,
  `auction_id` int(11) NOT NULL,
  `bidder_id` int(11) NOT NULL,
  `bid_amount` decimal(10,2) NOT NULL,
  `bid_time` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Bid`
--

INSERT INTO `Bid` (`bid_id`, `auction_id`, `bidder_id`, `bid_amount`, `bid_time`) VALUES
(1, 1, 1, 300.00, '2025-11-12 10:47:57'),
(2, 1, 4, 315.00, '2025-11-12 11:47:57'),
(3, 1, 1, 330.00, '2025-11-12 12:47:57'),
(4, 1, 4, 340.00, '2025-11-12 13:47:57'),
(5, 2, 1, 220.00, '2025-11-10 15:47:57'),
(6, 2, 4, 235.00, '2025-11-11 15:47:57'),
(7, 2, 1, 250.00, '2025-11-12 05:47:57'),
(8, 2, 1, 255.00, '2025-11-12 13:47:57'),
(9, 1, 1, 300.00, '2025-11-13 07:52:47'),
(10, 1, 4, 315.00, '2025-11-13 08:52:47'),
(11, 1, 1, 330.00, '2025-11-13 09:52:47'),
(12, 1, 4, 340.00, '2025-11-13 10:52:47'),
(13, 2, 1, 220.00, '2025-11-11 12:52:47'),
(14, 2, 4, 235.00, '2025-11-12 12:52:47'),
(15, 2, 1, 250.00, '2025-11-13 02:52:47'),
(16, 2, 1, 255.00, '2025-11-13 10:52:47'),
(17, 5, 4, 310.00, '2025-11-13 15:31:55');

-- --------------------------------------------------------

--
-- Table structure for table `Category`
--

CREATE TABLE `Category` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(255) NOT NULL,
  `category_description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Category`
--

INSERT INTO `Category` (`category_id`, `category_name`, `category_description`) VALUES
(1, 'Electronics', 'Electronic devices, gadgets, and accessories'),
(2, 'Fashion', 'Clothing, shoes, and accessories'),
(3, 'Home Appliances', 'Household and kitchen appliances'),
(4, 'Collectibles', 'Antiques, artwork, and rare items'),
(5, 'Beauty', 'Fragrance, makeup, hair and skincare'),
(6, 'Electronics', 'Electronic devices, gadgets, and accessories'),
(7, 'Fashion', 'Clothing, shoes, and accessories'),
(8, 'Home Appliances', 'Household and kitchen appliances'),
(9, 'Collectibles', 'Antiques, artwork, and rare items'),
(10, 'Beauty', 'Fragrance, makeup, hair and skincare');

-- --------------------------------------------------------

--
-- Table structure for table `Item`
--

CREATE TABLE `Item` (
  `item_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `item_description` text DEFAULT NULL,
  `seller_id` int(11) NOT NULL,
  `start_price` decimal(10,2) NOT NULL,
  `reserve_price` decimal(10,2) DEFAULT NULL,
  `status` enum('draft','published','sold','withdrawn') DEFAULT 'published',
  `category_id` int(11) DEFAULT NULL,
  `publish_time` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Item`
--

INSERT INTO `Item` (`item_id`, `title`, `item_description`, `seller_id`, `start_price`, `reserve_price`, `status`, `category_id`, `publish_time`) VALUES
(1, 'Apple iPhone 15 Pro', 'Brand new iPhone 15 Pro, 256GB, Titanium Gray, unlocked.', 2, 290.00, 350.00, 'published', 1, '2025-11-12 15:04:57'),
(2, 'Samsung QLED TV 65-inch', '4K UHD Smart TV with vibrant color and HDR10 support.', 2, 270.00, 330.00, 'published', 1, '2025-11-12 15:04:57'),
(3, 'Sony WH-1000XM5 Headphones', 'Noise-cancelling wireless headphones, black, near new.', 2, 225.00, 300.00, 'published', 1, '2025-11-12 15:04:57'),
(4, 'Gucci Leather Belt', 'Authentic Gucci black leather belt, size 90.', 2, 218.00, 250.00, 'published', 2, '2025-11-12 15:04:57'),
(5, 'Adidas Ultraboost 23', 'Men’s running shoes, size 43, brand new in box.', 2, 120.00, 150.00, 'published', 2, '2025-11-12 15:04:57'),
(6, 'Louis Vuitton Handbag', 'Pre-owned LV monogram handbag in great condition.', 2, 800.00, 950.00, 'published', 2, '2025-11-12 15:04:57'),
(7, 'Dyson Airwrap Complete', 'Multifunctional hair styler with all attachments.', 2, 350.00, 420.00, 'published', 3, '2025-11-12 15:04:57'),
(8, 'Philips Air Fryer XXL', '5.5L oil-free fryer with digital timer and temperature control.', 2, 150.00, 190.00, 'published', 3, '2025-11-12 15:04:57'),
(9, 'Vintage Rolex Submariner', '1975 original Rolex Submariner, stainless steel, excellent condition.', 2, 4000.00, 4800.00, 'published', 4, '2025-11-12 15:04:57'),
(10, 'Mickey Mouse Figurine 1960s', 'Rare vintage Disney collectible figure, perfect condition.', 2, 200.00, 280.00, 'published', 4, '2025-11-12 15:04:57'),
(11, 'Antique Chinese Porcelain Vase', 'Early 20th-century hand-painted porcelain, minor wear.', 2, 8000.00, 9500.00, 'published', 4, '2025-11-12 15:04:57'),
(12, 'Chanel No.5 Perfume', 'Classic 100ml Eau de Parfum, unopened.', 2, 300.00, 430.00, 'published', 5, '2025-11-12 15:04:57'),
(13, 'Estée Lauder Skincare Set', 'Includes moisturizer, serum, and eye cream, new in box.', 2, 85.00, 100.00, 'published', 5, '2025-11-12 15:04:57'),
(14, 'Apple iPhone 15 Pro', 'Brand new iPhone 15 Pro, 256GB, Titanium Gray, unlocked.', 2, 290.00, 350.00, 'published', 1, '2025-11-13 12:51:56'),
(15, 'Samsung QLED TV 65-inch', '4K UHD Smart TV with vibrant color and HDR10 support.', 2, 270.00, 330.00, 'published', 1, '2025-11-13 12:51:56'),
(16, 'Sony WH-1000XM5 Headphones', 'Noise-cancelling wireless headphones, black, near new.', 2, 225.00, 300.00, 'published', 1, '2025-11-13 12:51:56'),
(17, 'Gucci Leather Belt', 'Authentic Gucci black leather belt, size 90.', 2, 218.00, 250.00, 'published', 2, '2025-11-13 12:51:56'),
(18, 'Adidas Ultraboost 23', 'Men’s running shoes, size 43, brand new in box.', 2, 120.00, 150.00, 'published', 2, '2025-11-13 12:51:56'),
(19, 'Louis Vuitton Handbag', 'Pre-owned LV monogram handbag in great condition.', 2, 800.00, 950.00, 'published', 2, '2025-11-13 12:51:56'),
(20, 'Dyson Airwrap Complete', 'Multifunctional hair styler with all attachments.', 2, 350.00, 420.00, 'published', 3, '2025-11-13 12:51:56'),
(21, 'Philips Air Fryer XXL', '5.5L oil-free fryer with digital timer and temperature control.', 2, 150.00, 190.00, 'published', 3, '2025-11-13 12:51:56'),
(22, 'Vintage Rolex Submariner', '1975 original Rolex Submariner, stainless steel, excellent condition.', 2, 4000.00, 4800.00, 'published', 4, '2025-11-13 12:51:56'),
(23, 'Mickey Mouse Figurine 1960s', 'Rare vintage Disney collectible figure, perfect condition.', 2, 200.00, 280.00, 'published', 4, '2025-11-13 12:51:56'),
(24, 'Antique Chinese Porcelain Vase', 'Early 20th-century hand-painted porcelain, minor wear.', 2, 8000.00, 9500.00, 'published', 4, '2025-11-13 12:51:56'),
(25, 'Chanel No.5 Perfume', 'Classic 100ml Eau de Parfum, unopened.', 2, 300.00, 430.00, 'published', 5, '2025-11-13 12:51:56'),
(26, 'Estée Lauder Skincare Set', 'Includes moisturizer, serum, and eye cream, new in box.', 2, 85.00, 100.00, 'published', 5, '2025-11-13 12:51:56'),
(27, 'DJI Pocket3', 'very nice!!!!!', 2, 300.00, 320.00, 'published', 1, '2025-11-13 15:29:12');

-- --------------------------------------------------------

--
-- Table structure for table `ItemImage`
--

CREATE TABLE `ItemImage` (
  `image_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ItemImage`
--

INSERT INTO `ItemImage` (`image_id`, `item_id`, `image_url`, `is_primary`) VALUES
(1, 1, 'images/items/iphone15pro_main.jpg', 1),
(2, 1, 'images/items/iphone15pro_box.jpg', 0),
(3, 4, 'images/items/gucci_belt_main.jpg', 1),
(4, 4, 'images/items/gucci_belt_closeup.jpg', 0),
(5, 6, 'images/items/lv_bag_main.jpg', 1),
(6, 12, 'images/items/chanel_no5_main.jpg', 1),
(7, 12, 'images/items/chanel_no5_box.jpg', 0),
(8, 14, 'images/items/iphone15pro_main.jpg', 1),
(9, 14, 'images/items/iphone15pro_box.jpg', 0),
(10, 17, 'images/items/gucci_belt_main.jpg', 1),
(11, 17, 'images/items/gucci_belt_closeup.jpg', 0),
(12, 19, 'images/items/lv_bag_main.jpg', 1),
(13, 25, 'images/items/chanel_no5_main.jpg', 1),
(14, 25, 'images/items/chanel_no5_box.jpg', 0),
(15, 27, 'uploads/items/27/27_20251113_162912_185c30.jpg', 1);

-- --------------------------------------------------------

--
-- Table structure for table `Payment`
--

CREATE TABLE `Payment` (
  `payment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `auction_id` int(11) DEFAULT NULL,
  `item_id` int(11) NOT NULL,
  `paid_amount` decimal(10,2) NOT NULL,
  `payment_time` datetime DEFAULT current_timestamp(),
  `payment_method` enum('CreditCard','PayPal','BankTransfer','ApplePay') NOT NULL,
  `status` enum('pending','completed','failed') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Payment`
--

INSERT INTO `Payment` (`payment_id`, `user_id`, `auction_id`, `item_id`, `paid_amount`, `payment_time`, `payment_method`, `status`) VALUES
(1, 1, 2, 4, 255.00, '2025-11-12 15:28:02', 'PayPal', 'completed'),
(2, 4, 1, 1, 340.00, '2025-11-12 15:58:02', 'CreditCard', 'pending'),
(3, 1, NULL, 6, 950.00, '2025-11-11 15:58:02', 'BankTransfer', 'failed'),
(4, 1, 2, 4, 255.00, '2025-11-13 12:27:11', 'PayPal', 'completed'),
(5, 4, 1, 1, 340.00, '2025-11-13 12:57:11', 'CreditCard', 'pending'),
(6, 1, NULL, 6, 950.00, '2025-11-12 12:57:11', 'BankTransfer', 'failed');

-- --------------------------------------------------------

--
-- Table structure for table `Users`
--

CREATE TABLE `Users` (
  `user_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fName` varchar(100) DEFAULT NULL,
  `lName` varchar(100) DEFAULT NULL,
  `role` enum('buyer','seller','admin') NOT NULL DEFAULT 'buyer'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Users`
--

INSERT INTO `Users` (`user_id`, `email`, `username`, `password`, `fName`, `lName`, `role`) VALUES
(1, 'yy@example.com', 'yy1799', '1799forever', 'you', 'your', 'buyer'),
(2, 'Aventurine@example.com', 'shajin', 'yqxghpw666', 'Sha', 'Jin', 'seller'),
(3, 'Hugo@example.com', 'hugo', 'hugo1111', 'Hugo', 'A', 'admin'),
(4, 'david@example.com', 'davidbid', 'pass45678', 'David', 'Smith', 'buyer'),
(17, '3499648686@qq.com', 'Yuchen Xiang', '$2y$10$63up8yEO.aV2YSz1sgGCDuhL6HTY2vYyDty7E0vmlHWeqrMB.RugW', NULL, NULL, 'buyer');

-- --------------------------------------------------------

--
-- Table structure for table `Watchlist`
--

CREATE TABLE `Watchlist` (
  `watch_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `auction_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Watchlist`
--

INSERT INTO `Watchlist` (`watch_id`, `user_id`, `auction_id`, `created_at`) VALUES
(1, 1, 1, '2025-11-12 10:52:53'),
(2, 4, 1, '2025-11-12 12:52:53'),
(3, 1, 2, '2025-11-10 15:52:53'),
(4, 4, 2, '2025-11-11 15:52:53'),
(5, 1, 1, '2025-11-13 07:53:04'),
(6, 4, 1, '2025-11-13 09:53:04'),
(7, 1, 2, '2025-11-11 12:53:04'),
(8, 4, 2, '2025-11-12 12:53:04');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `Auction`
--
ALTER TABLE `Auction`
  ADD PRIMARY KEY (`auction_id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `winner_id` (`winner_id`);

--
-- Indexes for table `Bid`
--
ALTER TABLE `Bid`
  ADD PRIMARY KEY (`bid_id`),
  ADD KEY `auction_id` (`auction_id`),
  ADD KEY `bidder_id` (`bidder_id`);

--
-- Indexes for table `Category`
--
ALTER TABLE `Category`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `Item`
--
ALTER TABLE `Item`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `seller_id` (`seller_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `ItemImage`
--
ALTER TABLE `ItemImage`
  ADD PRIMARY KEY (`image_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `Payment`
--
ALTER TABLE `Payment`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `auction_id` (`auction_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `Users`
--
ALTER TABLE `Users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `Watchlist`
--
ALTER TABLE `Watchlist`
  ADD PRIMARY KEY (`watch_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `auction_id` (`auction_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `Auction`
--
ALTER TABLE `Auction`
  MODIFY `auction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `Bid`
--
ALTER TABLE `Bid`
  MODIFY `bid_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `Category`
--
ALTER TABLE `Category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `Item`
--
ALTER TABLE `Item`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `ItemImage`
--
ALTER TABLE `ItemImage`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `Payment`
--
ALTER TABLE `Payment`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `Users`
--
ALTER TABLE `Users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `Watchlist`
--
ALTER TABLE `Watchlist`
  MODIFY `watch_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `Auction`
--
ALTER TABLE `Auction`
  ADD CONSTRAINT `auction_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `Item` (`item_id`),
  ADD CONSTRAINT `auction_ibfk_2` FOREIGN KEY (`winner_id`) REFERENCES `Users` (`user_id`);

--
-- Constraints for table `Bid`
--
ALTER TABLE `Bid`
  ADD CONSTRAINT `bid_ibfk_1` FOREIGN KEY (`auction_id`) REFERENCES `Auction` (`auction_id`),
  ADD CONSTRAINT `bid_ibfk_2` FOREIGN KEY (`bidder_id`) REFERENCES `Users` (`user_id`);

--
-- Constraints for table `Item`
--
ALTER TABLE `Item`
  ADD CONSTRAINT `item_ibfk_1` FOREIGN KEY (`seller_id`) REFERENCES `Users` (`user_id`),
  ADD CONSTRAINT `item_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `Category` (`category_id`);

--
-- Constraints for table `ItemImage`
--
ALTER TABLE `ItemImage`
  ADD CONSTRAINT `itemimage_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `Item` (`item_id`);

--
-- Constraints for table `Payment`
--
ALTER TABLE `Payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`),
  ADD CONSTRAINT `payment_ibfk_2` FOREIGN KEY (`auction_id`) REFERENCES `Auction` (`auction_id`),
  ADD CONSTRAINT `payment_ibfk_3` FOREIGN KEY (`item_id`) REFERENCES `Item` (`item_id`);

--
-- Constraints for table `Watchlist`
--
ALTER TABLE `Watchlist`
  ADD CONSTRAINT `watchlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`),
  ADD CONSTRAINT `watchlist_ibfk_2` FOREIGN KEY (`auction_id`) REFERENCES `Auction` (`auction_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
