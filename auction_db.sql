-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Nov 27, 2025 at 04:02 PM
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
  `status` enum('Upcoming','Ongoing','Completed','Failed') DEFAULT 'Upcoming'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Auction`
--

INSERT INTO `Auction` (`auction_id`, `item_id`, `start_date`, `end_date`, `winner_id`, `final_price`, `current_price`, `status`) VALUES
(1, 1, '2025-11-12 14:25:25', '2025-11-13 15:25:25', NULL, NULL, 290.00, 'Failed'),
(2, 4, '2025-11-09 15:25:25', '2025-11-12 14:25:25', 1, 255.00, 255.00, 'Completed'),
(5, 27, '2025-11-13 16:29:00', '2025-11-14 16:29:00', NULL, NULL, 310.00, 'Failed'),
(6, 28, '2025-11-20 11:58:00', '2025-11-27 11:58:00', NULL, NULL, 1970.00, 'Ongoing'),
(7, 29, '2025-11-22 14:51:00', '2025-11-29 14:51:00', NULL, NULL, 110.00, 'Upcoming'),
(8, 32, '2025-11-26 15:43:00', '2025-12-03 15:43:00', NULL, NULL, 300.00, 'Ongoing'),
(9, 37, '2025-12-01 20:00:00', '2025-12-05 20:00:00', NULL, NULL, 800.00, 'Ongoing'),
(11, 39, '2025-11-26 18:00:00', '2025-12-05 18:00:00', NULL, NULL, 900.00, 'Ongoing'),
(13, 40, '2025-12-05 11:11:00', '2025-12-10 11:11:00', NULL, NULL, 25.00, 'Ongoing');

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
(24, 6, 1, 1960.00, '2025-11-22 11:39:53'),
(26, 6, 4, 1970.00, '2025-11-22 12:08:34');

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
(5, 'Beauty', 'Fragrance, makeup, hair and skincare');

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
(4, 'Gucci Leather Belt', 'Authentic Gucci black leather belt, size 90.', 2, 218.00, 250.00, 'published', 2, '2025-11-12 15:04:57'),
(27, 'DJI Pocket3', 'very nice!!!!!', 2, 300.00, 320.00, 'published', 1, '2025-11-13 15:29:12'),
(28, 'Burberry', 'Beautiful windbreaker', 17, 1895.00, 1950.00, 'published', 2, '2025-11-20 10:58:54'),
(29, 'cat', 'very cute!!!', 2, 110.00, 120.00, 'published', 3, '2025-11-20 13:51:57'),
(32, 'Dyson Hair Dryer', 'Dyson high-speed hair dryer. Comes with all original attachments, used lightly and fully functional.\r\nColor: Iron/Fuchsia.', 18, 300.00, 350.00, 'published', 1, '2025-11-21 22:02:39'),
(35, 'BYREDO ROSE OF NO MAN\'S LAND EAU DE PARFUM 50ML', 'Immerse your senses in this bold and delicate Rose of No Man’s Land Eau De Partum from the personal fragrance range at Byredo. It was developed as a tribute to the brave, fearless nurses of World War I, who tended to injured soldiers in the heat of conflict, quickly earning themselves the name Roses of No Man’s Land.', 18, 100.00, 120.00, 'published', 5, '2025-11-26 16:07:15'),
(36, 'VICTORIA BECKHAM BEAUTY THE FOUNDATION DROPS', 'Achieve a natural, luminous finish with Victoria Beckham The Foundation Drops. The light, serum foundation offers buildable coverage while nourishing skin via a combination of powerful nutrients.', 18, 80.00, 100.00, 'published', 5, '2025-11-26 16:23:43'),
(37, 'Apple MacBook Air 13 irch (M4, 16GB RAM, 512GB SSD) - Midnight (second-hand）', 'The Apple MacBook Air 13\" with M4 chip delivers next-level performance in an ultra-thin, ultra-light design. Powered by Apple’s latest M4 chip, this model is faster, more efficient, and perfectly suited for everything from everyday tasks to creative workflows.\r\nWith 16GB of unified memory and a 512GB SSD, it handles multitasking, large files, and demanding apps with ease. The stunning 13.6-inch Liquid Retina display offers vivid colors and sharp detail, while the all-day battery life keeps you going wherever you are.', 18, 800.00, 850.00, 'published', 1, '2025-11-26 18:02:46'),
(39, 'SONY WH-1000XM4 Headphone', 'The WH-1000XM4 headphones offer improved industry-leading noise cancellation capable of adapting to location as well as behaviour, further improved sound quality through DSEE Extreme and LDAC, and even smarter features such as speak-to-chat functionality, built in Voice Assistant and gesture control, to deliver a flawlessly premium listening experience on our most intelligent headphones yet. Plus, with a huge 30-hour battery life you listen day and night to your tunes in extreme weightless comfort uninterrupted.', 2, 900.00, 1000.00, 'published', 1, '2025-11-26 18:08:52'),
(40, 'Oral-B iO2 Electric TOothbrush and Travel Case, 3 quiet modes, Night Black', 'Elevate your daily dental care routine with the Oral B iO2 Electric Toothbrush – Black, C010174 – a sleek, precision‑designed tool for cleaner teeth and healthier gums. Featuring a dentist‑inspired round brush head and gentle micro‑vibrations, this model offers three intensity modes and a built‑in automatic pressure sensor that alerts you when you brush too hard, helping protect your teeth and gums effectively. A 2‑minute timer with 30‑second zone reminders ensures you cover every part of your mouth properly.', 2, 25.00, 30.00, 'published', 1, '2025-11-27 11:11:02');

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
(15, 27, 'uploads/items/27/27_djPocket3.jpg', 1),
(16, 28, 'uploads/items/28/28_20251120_115854_04ae75.png', 1),
(17, 29, 'uploads/items/29/29_20251120_145157_197c5a.jpg', 1),
(18, 1, 'uploads/items/1/1_Iphone15Pro.jpg\r\n', 1),
(19, 4, 'uploads/items/4/4_GucciLeatherBelt.jpg\r\n', 0),
(20, 32, 'uploads/items/32/32_20251121_230239_82f1a0.jpg', 1),
(21, 35, 'uploads/items/35/35_20251126_170715_2bea5a.png', 1),
(22, 36, 'uploads/items/36/36_20251126_172343_d9f304.png', 1),
(23, 37, 'uploads/items/37/37_20251126_190246_4fde5f.jpg', 1),
(25, 39, 'uploads/items/39/39_20251126_190852_8727ba.jpg', 1),
(26, 40, 'uploads/items/40/40_20251127_121102_7a1964.jpg', 1);

-- --------------------------------------------------------

--
-- Table structure for table `Notifications`
--

CREATE TABLE `Notifications` (
  `message_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Notifications`
--

INSERT INTO `Notifications` (`message_id`, `user_id`, `message`, `is_read`, `created_at`) VALUES
(1, 1, 'You were outbid: Another buyer placed a higher bid (£1970) on \'Burberry\'.', 1, '2025-11-22 12:08:34'),
(2, 17, 'New bid on item: A new bid (£1970) was placed on \'Burberry\'.', 1, '2025-11-22 12:08:34'),
(3, 1, 'New bid on item: A new bid (£1970) was placed on \'Burberry\'.', 1, '2025-11-22 12:08:34'),
(4, 4, 'New bid on item: A new bid (£1970) was placed on \'Burberry\'.', 0, '2025-11-22 12:08:34'),
(5, 17, 'Your auction received a bid: A buyer placed a new bid (£1970) on your item \'Burberry\'.', 1, '2025-11-22 12:08:34');

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
(1, 'yy@example.com', 'buyerB', 'pwbuyerb', 'you', 'your', 'buyer'),
(2, 'Aventurine@example.com', 'sellerB', 'pwsellerb', 'Sha', 'Jin', 'seller'),
(3, 'Hugo@example.com', 'hugo', 'hugo1111', 'Hugo', 'A', 'admin'),
(4, 'david@example.com', 'davidbid', 'pass45678', 'David', 'Smith', 'buyer'),
(17, '3499648686@qq.com', 'Yuchen Xiang', '$2y$10$63up8yEO.aV2YSz1sgGCDuhL6HTY2vYyDty7E0vmlHWeqrMB.RugW', NULL, NULL, 'buyer'),
(18, '2717846877@qq.com', 'LXY', '$2y$10$uLz.4dcC5121odkwDtwWaeTWZpTNBkihlCwkHT226LkuRul.RbbrS', NULL, NULL, 'seller');

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
(2, 4, 1, '2025-11-12 12:52:53'),
(4, 4, 2, '2025-11-11 15:52:53'),
(14, 4, 6, '2025-11-22 10:57:50'),
(20, 1, 8, '2025-11-26 16:13:59'),
(21, 17, 9, '2025-11-26 18:04:25');

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
-- Indexes for table `Notifications`
--
ALTER TABLE `Notifications`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `user_id` (`user_id`);

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
  MODIFY `auction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `Bid`
--
ALTER TABLE `Bid`
  MODIFY `bid_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `Category`
--
ALTER TABLE `Category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `Item`
--
ALTER TABLE `Item`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `ItemImage`
--
ALTER TABLE `ItemImage`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `Notifications`
--
ALTER TABLE `Notifications`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `Users`
--
ALTER TABLE `Users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `Watchlist`
--
ALTER TABLE `Watchlist`
  MODIFY `watch_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

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
-- Constraints for table `Notifications`
--
ALTER TABLE `Notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`);

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
