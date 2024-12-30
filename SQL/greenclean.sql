-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 30, 2024 at 05:13 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `greenclean`
--

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE `contacts` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `submission_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contacts`
--

INSERT INTO `contacts` (`id`, `name`, `email`, `message`, `submission_date`) VALUES
(1, 'Name1', 'user1@gmail.com', 'TestTextTestTextTestTextTestTextTestTextTestTextTestTextTestTextTestTextTestText', '2024-12-25 20:11:26'),
(2, 'Name1', 'user1@gmail.com', 'TestTextTestTextTestTextTestTextTestTextTestTextTestTextTestTextTestTextTestText', '2024-12-25 20:12:19'),
(3, 'Name2', 'user2@gmail.com', 'TestTextTestTextTestTextTestTextTestTextTestTextTestTextTestTextTestTextTestText', '2024-12-25 20:15:03');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `shipping_address` varchar(255) DEFAULT NULL,
  `shipping_city` varchar(100) DEFAULT NULL,
  `shipping_state` varchar(50) DEFAULT NULL,
  `shipping_zip` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_amount`, `status`, `created_at`, `updated_at`, `shipping_address`, `shipping_city`, `shipping_state`, `shipping_zip`) VALUES
(16, 1, 17.99, 'completed', '2024-12-28 15:57:20', '2024-12-28 16:15:07', 'dhaka', 'Dhaka', 'dhaka', '12344'),
(17, 1, 16.97, 'cancelled', '2024-12-28 15:58:06', '2024-12-28 16:34:08', 'dhaka', 'Dhaka', 'dhaka', '12344'),
(18, 17, 11.99, 'completed', '2024-12-28 16:02:49', '2024-12-29 16:42:17', 'House no -2, Road -2', 'Dhaka', 'ctg', '12345'),
(19, 17, 13.99, 'processing', '2024-12-28 16:13:31', '2024-12-29 16:42:23', 'House no -2, Road -2', 'Dhaka', 'ctg', '12344'),
(20, 17, 13.99, 'confirmed', '2024-12-28 16:34:39', '2024-12-29 16:42:30', 'House no -2, Road -2', 'Dhaka', 'ctg', '12344'),
(21, 17, 14.99, 'confirmed', '2024-12-28 16:37:45', '2024-12-29 16:42:26', 'House no -2, Road -2', 'Dhaka', 'ctg', '12344'),
(23, 1, 13.99, 'confirmed', '2024-12-29 15:08:17', '2024-12-29 16:42:02', 'House no -1, Road -1', 'Dhaka', 'ctg', '12344'),
(24, 1, 14.99, 'confirmed', '2024-12-29 15:08:39', '2024-12-29 16:41:59', 'House no -1, Road -1', 'Dhaka', 'ctg', '12344'),
(26, 1, 8.49, 'confirmed', '2024-12-29 15:15:38', '2024-12-29 16:41:56', 'House no -1, Road -1', 'Dhaka', 'ctg', '12344'),
(27, 1, 9.99, 'confirmed', '2024-12-29 15:18:54', '2024-12-29 16:41:53', 'House no -1, Road -1', 'Dhaka', 'ctg', '12344'),
(28, 1, 13.99, 'confirmed', '2024-12-29 16:40:44', '2024-12-29 16:41:25', 'House no -1, Road -1', 'Dhaka', 'Ctg', '12345'),
(31, 1, 47.97, 'confirmed', '2024-12-30 14:20:20', '2024-12-30 14:20:44', 'House no -1, Road -1', 'Dhaka', 'Ctg', '12344'),
(32, 1, NULL, 'pending', '2024-12-30 15:38:04', '2024-12-30 16:08:26', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(45, 16, 3, 1, 12.99),
(46, 17, 4, 3, 3.99),
(49, 18, 9, 1, 6.99),
(50, 19, 2, 1, 8.99),
(51, 20, 2, 1, 8.99),
(53, 21, 1, 1, 9.99),
(54, 22, 2, 2, 8.99),
(56, 23, 2, 1, 8.99),
(57, 24, 1, 1, 9.99),
(58, 25, 8, 1, 3.49),
(59, 26, 8, 1, 3.49),
(60, 27, 7, 1, 4.99),
(61, 28, 2, 1, 8.99),
(63, 30, 11, 3, 15.99),
(66, 30, 10, 1, 10.99),
(67, 31, 11, 2, 15.99),
(69, 31, 10, 1, 10.99);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `stock` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `image_url`, `category`, `stock`, `created_at`, `updated_at`) VALUES
(1, 'All-Purpose Cleaner', 'A versatile cleaner for all surfaces, made with natural ingredients.', 9.99, '/images/products/all-purpose.webp', 'household', 53, '2024-12-24 22:58:12', '2024-12-29 15:08:41'),
(2, 'Glass Cleaner', 'Streak-free shine for all your glass surfaces, without harsh chemicals.', 8.99, '/images/products/glass-cleaner.jpg', 'household', 86, '2024-12-24 22:58:12', '2024-12-29 16:40:47'),
(3, 'Natural Deodorant', 'Aluminum-free deodorant with a fresh scent.', 12.99, '/images/products/deodorant.webp', 'personal', 80, '2024-12-24 22:58:12', '2024-12-28 15:57:25'),
(4, 'Bamboo Toothbrush', 'Eco-friendly toothbrush made from sustainable bamboo.', 3.99, '/images/products/eco-friendly-bamboo-tooth-brush.png', 'personal', 95, '2024-12-24 22:58:12', '2024-12-28 15:58:10'),
(5, 'Face Mask', 'Rejuvenating face mask made with organic ingredients.', 7.99, '/images/products/face-mask.jpg', 'personal', 99, '2024-12-24 22:58:12', '2024-12-28 14:17:20'),
(6, 'Wooden Hair Comb', 'Handcrafted wooden comb suitable for all hair types.', 5.99, '/images/products/hair-comb-wooden-comb.webp', 'personal', 100, '2024-12-24 22:58:12', '2024-12-25 19:16:45'),
(7, 'Handmade Natural Soap', 'Chemical-free soap bar made from essential oils.', 4.99, '/images/products/handmade-natural-soap-icon.jpg', 'personal', 96, '2024-12-24 22:58:12', '2024-12-29 15:18:57'),
(8, 'Lip Balm', 'Nourishing lip balm made with organic ingredients.', 3.49, '/images/products/lip-balm.jpg', 'personal', 98, '2024-12-24 22:58:12', '2024-12-29 15:15:41'),
(9, 'Charcoal Bamboo Scrub', 'Organic, biodegradable scrub for a deep cleanse.', 6.99, '/images/products/organic-biodegradable-charcoal-bamboo.jpg', 'personal', 99, '2024-12-24 22:58:12', '2024-12-28 16:02:53'),
(10, 'Reusable Makeup Remover Pads', 'Soft, washable makeup remover pads.', 10.99, '/images/products/reusable-makeup-remover-pads.jpg', 'personal', 98, '2024-12-24 22:58:12', '2024-12-30 14:20:25'),
(11, 'Shampoo & Conditioner', 'Sulfate-free shampoo and conditioner set.', 15.99, '/images/products/Shampoo&Conditioner.jpeg', 'personal', 95, '2024-12-24 22:58:12', '2024-12-30 14:20:25'),
(12, 'Reusable Metal Razor', 'Eco-friendly razor with replaceable blades.', 19.99, '/images/products/razor.jpg', 'personal', 99, '2024-12-24 22:58:12', '2024-12-28 15:16:31');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_admin` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `created_at`, `updated_at`, `is_admin`) VALUES
(1, 'user1', 'user1@gmail.com', 'user1', '2024-12-24 22:58:12', '2024-12-30 15:37:58', 0),
(2, 'admin1', 'admin1@gmail.com', 'admin1', '2024-12-24 23:21:03', '2024-12-29 16:50:25', 1),
(17, 'user2', 'user2@gmail.com', 'user2', '2024-12-27 14:19:45', '2024-12-29 16:48:15', 0),
(19, 'admin2', 'admin2@example.com', 'admin2', '2024-12-28 17:27:27', '2024-12-28 17:27:27', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_order_product` (`order_id`,`product_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
