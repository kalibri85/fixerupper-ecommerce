-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jun 25, 2026 at 01:20 AM
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
-- Database: `fixerUpperDB`
--

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

CREATE TABLE `addresses` (
  `id` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `fullName` varchar(255) NOT NULL,
  `phone` varchar(14) NOT NULL,
  `address` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `postcode` varchar(10) NOT NULL,
  `country` varchar(255) NOT NULL,
  `billing` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `addresses`
--

INSERT INTO `addresses` (`id`, `userID`, `fullName`, `phone`, `address`, `city`, `postcode`, `country`, `billing`) VALUES
(1, 3, 'Mulana Mars', '', '121 Tudor road', 'Leicester', 'LE3 5JJ', 'United Kingdom', 1),
(4, 3, 'Mulana Mars', '', '102 some add', 'Leicester', 'LE1 3BK', 'United Kingdom', 0),
(5, 4, 'John Li', '07456589', '121 Tudor road', 'Leicester', 'LE3 5JJ', 'United Kingdom', 1),
(6, 5, 'ksddsf vbghmjvgbkm', '', '121 Tudor road', 'Leicester', 'LE3 5JJ', 'United Kingdom', 1),
(7, 6, 'Kira Malevich', '', '119 Tudor road', 'London', 'ER1 6LK', 'United Kingdom', 1),
(8, 7, 'Parmy Munder', '', '117 Tudor road', 'Leicester', 'LE3 1JJ', 'United Kingdom', 1),
(9, 8, 'Neil Juego', '', '99 Tudor road', 'Leicester', 'LE9 9DE', 'United Kingdom', 1),
(10, 8, 'Neil Juego', '', '28 Bothword road', 'Leicester', 'LE7 8DE', 'United Kingdom', 0),
(11, 9, 'Zuhra Amazon', '', '110 Tudor road', 'Rochester', 'RC5 6JK', 'United Kingdom', 1),
(12, 10, 'Gracy Turner', '', '107 Tudor road', 'Leicester', 'LE3 1JJ', 'United Kingdom', 1),
(13, 10, 'Gracy Turner', '', '28 Bothword road', 'Leicester', 'LE7 8DE', 'United Kingdom', 0);

-- --------------------------------------------------------

--
-- Table structure for table `attributes`
--

CREATE TABLE `attributes` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attributes`
--

INSERT INTO `attributes` (`id`, `name`) VALUES
(10, 'Colour'),
(12, 'Pack quantity'),
(13, 'Power'),
(15, 'Type'),
(25, 'Capacity'),
(26, 'Material'),
(27, 'Keep warm function'),
(28, 'Weight'),
(30, 'Type');

-- --------------------------------------------------------

--
-- Table structure for table `attributes_category`
--

CREATE TABLE `attributes_category` (
  `id` int(11) NOT NULL,
  `categoryID` int(11) NOT NULL,
  `attributeID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attributes_category`
--

INSERT INTO `attributes_category` (`id`, `categoryID`, `attributeID`) VALUES
(43, 2, 26),
(44, 6, 26),
(45, 12, 26),
(46, 22, 26),
(47, 23, 26),
(48, 24, 26),
(49, 25, 26),
(50, 2, 27),
(51, 6, 27),
(52, 12, 27),
(67, 31, 28),
(68, 25, 15),
(69, 28, 15),
(70, 29, 15),
(71, 30, 15),
(72, 2, 25),
(73, 6, 25),
(74, 12, 25),
(75, 24, 25),
(76, 25, 25),
(78, 34, 30),
(79, 2, 10),
(80, 6, 10),
(81, 12, 10),
(82, 22, 10),
(83, 23, 10),
(84, 24, 10),
(85, 25, 10),
(86, 28, 10),
(87, 29, 10),
(88, 30, 10),
(89, 35, 10),
(90, 2, 13),
(91, 6, 13),
(92, 12, 13),
(93, 22, 13),
(94, 24, 13),
(95, 25, 13),
(96, 35, 13);

-- --------------------------------------------------------

--
-- Table structure for table `attributes_product`
--

CREATE TABLE `attributes_product` (
  `id` int(11) NOT NULL,
  `productID` int(11) NOT NULL,
  `attributeID` int(11) NOT NULL,
  `valueID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attributes_product`
--

INSERT INTO `attributes_product` (`id`, `productID`, `attributeID`, `valueID`) VALUES
(1, 1, 13, 21),
(2, 1, 13, 22),
(3, 2, 13, 21),
(4, 2, 13, 22),
(38, 22, 13, 20),
(39, 22, 13, 21),
(40, 22, 13, 22),
(65, 23, 10, 1),
(66, 23, 10, 7),
(70, 26, 13, 21),
(74, 25, 15, 27),
(125, 20, 13, 39),
(126, 20, 25, 38),
(127, 20, 10, 8),
(128, 20, 10, 30),
(131, 24, 13, 37),
(132, 24, 10, 1),
(133, 24, 10, 36),
(134, 24, 25, 32),
(135, 5, 13, 31),
(136, 5, 25, 40),
(140, 28, 10, 7),
(141, 28, 15, 27),
(152, 30, 15, 27),
(153, 30, 10, 6),
(155, 31, 15, 27),
(156, 31, 10, 6),
(157, 29, 15, 26),
(158, 29, 10, 7),
(159, 32, 28, 42),
(161, 33, 28, 43),
(164, 35, 13, 31),
(170, 36, 26, 1),
(171, 36, 13, 1),
(172, 34, 13, 45),
(173, 34, 10, 8),
(174, 34, 25, 46),
(175, 3, 13, 22),
(176, 3, 25, 41),
(189, 27, 13, 31),
(190, 27, 26, 33),
(191, 27, 27, 34),
(192, 27, 10, 1),
(193, 27, 10, 30),
(194, 27, 25, 32),
(195, 37, 28, 55),
(196, 38, 10, 1),
(197, 38, 13, 1);

-- --------------------------------------------------------

--
-- Table structure for table `attribute_values`
--

CREATE TABLE `attribute_values` (
  `id` int(11) NOT NULL,
  `attributeID` int(11) NOT NULL,
  `value` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attribute_values`
--

INSERT INTO `attribute_values` (`id`, `attributeID`, `value`) VALUES
(1, 10, 'Red'),
(6, 10, 'Blue'),
(7, 10, 'Green'),
(8, 10, 'White'),
(11, 10, 'Yellow'),
(12, 12, '1'),
(13, 12, '2'),
(14, 12, '4'),
(16, 12, '10'),
(17, 10, 'Pink'),
(20, 13, '300 W'),
(21, 13, '700 W'),
(22, 13, '800 W'),
(26, 15, 'Corded'),
(27, 15, 'Cordless'),
(30, 10, 'Black'),
(31, 13, '2200 W'),
(32, 25, '1.7 litres'),
(33, 26, 'Metal/stainless steel'),
(34, 27, 'Yes'),
(35, 27, 'No'),
(36, 10, 'Graphite'),
(37, 13, '3000W'),
(38, 25, '7.2 litres'),
(39, 13, '1800 W'),
(40, 25, '8 litres'),
(41, 25, '2.3 litres'),
(42, 28, '0.465 kg'),
(43, 28, '1 kg'),
(44, 15, 'Robot'),
(45, 13, '33 W'),
(46, 25, '0.5 litre'),
(47, 25, '1.5 litre'),
(48, 25, '4.5 litres'),
(51, 10, 'Orange'),
(52, 30, 'Stand Mixer'),
(53, 30, 'Hand Mixer'),
(54, 30, 'Kitchen Machine'),
(55, 28, '1.230 kg'),
(56, 26, 'Nylon');

-- --------------------------------------------------------

--
-- Table structure for table `brands`
--

CREATE TABLE `brands` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `brands`
--

INSERT INTO `brands` (`id`, `name`) VALUES
(1, 'Bosch'),
(3, 'Makita'),
(4, 'AEG'),
(5, 'Beko'),
(6, 'Samsung'),
(10, 'Philips'),
(14, 'KARCHER'),
(16, 'KENWOOD'),
(17, 'Russell Hobbs'),
(18, 'KitchenAid');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `parent` int(11) NOT NULL,
  `category` varchar(255) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `parent`, `category`, `active`) VALUES
(2, 24, 'Small kitchen appliances', 1),
(6, 2, 'Air fryers', 1),
(12, 2, 'Kettles', 1),
(22, 0, 'Power Tools', 1),
(23, 0, 'Hand Tools', 1),
(24, 0, 'Appliances', 1),
(25, 24, 'Vacuum cleaners', 1),
(28, 22, 'Drills', 1),
(29, 22, 'Jigsaws', 1),
(30, 22, 'Impact drivers & wrenches', 1),
(31, 23, 'Hammers', 1),
(34, 2, 'Food mixers', 0),
(35, 2, 'Toasters', 1);

-- --------------------------------------------------------

--
-- Table structure for table `delivery`
--

CREATE TABLE `delivery` (
  `id` int(11) NOT NULL,
  `orderID` int(11) NOT NULL,
  `addressID` int(11) NOT NULL,
  `methodID` int(11) NOT NULL,
  `status` enum('shipped','delivered','failed') DEFAULT NULL,
  `shipped_at` datetime DEFAULT NULL,
  `delivered_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `delivery`
--

INSERT INTO `delivery` (`id`, `orderID`, `addressID`, `methodID`, `status`, `shipped_at`, `delivered_at`) VALUES
(1, 2, 1, 2, 'delivered', '2026-05-11 00:56:30', '2026-05-11 00:56:55'),
(2, 3, 4, 2, 'delivered', '2026-05-13 21:09:55', '2026-06-21 00:15:44'),
(3, 4, 1, 1, 'delivered', '2026-05-09 02:15:36', '2026-05-11 00:54:15'),
(4, 5, 5, 1, 'delivered', '2026-05-13 21:30:56', '2026-05-13 21:53:29'),
(5, 6, 5, 1, 'delivered', '2026-06-24 17:31:32', '2026-06-24 17:31:54'),
(6, 7, 5, 2, 'delivered', NULL, '2026-05-21 12:15:39'),
(7, 8, 6, 1, 'delivered', NULL, '2026-06-11 12:37:16'),
(8, 9, 7, 2, 'delivered', NULL, '2026-06-20 01:43:35'),
(9, 10, 8, 2, 'delivered', NULL, '2026-06-21 00:18:20'),
(10, 11, 10, 2, 'delivered', NULL, '2026-06-21 21:04:13'),
(11, 12, 11, 1, 'delivered', '2026-06-23 22:23:22', '2026-06-23 22:56:03'),
(12, 13, 11, 2, 'delivered', NULL, '2026-06-23 20:45:20'),
(13, 14, 13, 2, 'delivered', NULL, '2026-06-24 16:29:22');

-- --------------------------------------------------------

--
-- Table structure for table `delivery_method`
--

CREATE TABLE `delivery_method` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `price` double NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `delivery_method`
--

INSERT INTO `delivery_method` (`id`, `title`, `price`, `active`) VALUES
(1, 'Click & Collect', 0, 1),
(2, 'Courier Delivery', 5.99, 1),
(8, 'esgdg', 0.03, 2),
(9, 'fftyfty', 10, 2),
(10, 'new method', 0, 2),
(11, 'new delivery', 10, 2);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `deliveryID` int(11) DEFAULT NULL,
  `peymentMethodID` int(11) NOT NULL,
  `status` enum('pending','paid','shipped','completed','cancelled') NOT NULL DEFAULT 'pending',
  `totalItems` double NOT NULL,
  `deliveryPrice` double NOT NULL,
  `total` double NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `userID`, `deliveryID`, `peymentMethodID`, `status`, `totalItems`, `deliveryPrice`, `total`, `created_at`) VALUES
(2, 3, 1, 2, 'completed', 1, 4.99, 74.97999999999999, '2026-05-08 11:17:09'),
(3, 3, 2, 1, 'completed', 1, 4.99, 303.99, '2026-05-08 11:18:01'),
(4, 3, 3, 2, 'completed', 3, 0, 150, '2026-05-08 11:19:48'),
(5, 4, 4, 2, 'completed', 1, 0, 299, '2026-05-12 12:19:15'),
(6, 4, 5, 1, 'completed', 1, 0, 69.99, '2026-05-13 20:50:25'),
(7, 4, 6, 2, 'completed', 4, 4.99, 284.97, '2026-05-21 10:59:37'),
(8, 5, 7, 1, 'completed', 4, 0, 146.33999999999997, '2026-06-11 11:36:33'),
(9, 6, 8, 2, 'completed', 1, 4.99, 64.98, '2026-06-19 23:50:42'),
(10, 7, 9, 2, 'completed', 1, 4.99, 84.97999999999999, '2026-06-20 23:07:25'),
(11, 8, 10, 1, 'completed', 2, 5.99, 125.97, '2026-06-21 19:25:45'),
(12, 9, 11, 1, 'completed', 1, 0, 69.99, '2026-06-23 16:17:56'),
(13, 9, 12, 1, 'completed', 1, 5.99, 21.98, '2026-06-23 19:04:21'),
(14, 10, 13, 2, 'completed', 1, 5.99, 65.98, '2026-06-24 15:27:39');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `orderID` int(11) NOT NULL,
  `productID` int(11) NOT NULL,
  `variation_label` varchar(255) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `price` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `orderID`, `productID`, `variation_label`, `quantity`, `price`) VALUES
(1, 2, 27, NULL, 1, 69.99),
(2, 3, 25, NULL, 1, 299),
(3, 4, 20, NULL, 3, 50),
(4, 5, 25, NULL, 1, 299),
(5, 6, 27, NULL, 1, 69.99),
(6, 7, 27, NULL, 2, 69.99),
(7, 7, 29, NULL, 2, 70),
(8, 8, 27, NULL, 1, 69.99),
(9, 8, 33, NULL, 3, 25.45),
(10, 9, 27, 'Colour: Black', 1, 59.99),
(11, 10, 5, '', 1, 79.99),
(12, 11, 27, 'Colour: Black', 2, 59.99),
(13, 12, 27, 'Colour: Red', 1, 69.99),
(14, 13, 32, '', 1, 15.99),
(15, 14, 27, 'Colour: Black', 1, 59.99);

-- --------------------------------------------------------

--
-- Table structure for table `payment_methods`
--

CREATE TABLE `payment_methods` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_methods`
--

INSERT INTO `payment_methods` (`id`, `title`) VALUES
(1, 'Pay on Collection'),
(2, 'Card Payment');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `categoryID` int(11) DEFAULT NULL,
  `brandID` int(11) DEFAULT NULL,
  `sku` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `price` double NOT NULL,
  `qty` int(11) NOT NULL,
  `status` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `categoryID`, `brandID`, `sku`, `name`, `description`, `image`, `price`, `qty`, `status`) VALUES
(1, 6, 1, 'b3ai', 'New Product', 'the best air fryer', '1776201060_emtronics-8l-dual-air-fryer-with-timer-grey~5056149883542_01c_MP.webp', 120.99, 1, 2),
(2, 6, 5, 'b3aig', 'New ope', '<p><strong>one</strong> one efuijkanvkjfdn eklafcmjlksdvmklzfd kadcma</p>\r\n<ul>\r\n<li>fadfvg</li>\r\n<li>dfvgdfgf</li>\r\n<li>fdfgsf</li>\r\n</ul>', '1776202185_emtronics-8l-dual-air-fryer-with-timer-grey~5056149883542_01c_MP.webp', 59.99, 20, 2),
(3, 6, 4, 'vgfdxfbgf', 'Air Fryer', '<p>- Cook up tasty, healthier fried treats with this <strong>800 W</strong>&nbsp;Quest manual air fryer<br>-&nbsp;Choose from&nbsp;<strong>8</strong><strong>&nbsp;presets</strong>&nbsp;including fries, pizza or cake and let it take care of the rest<br>- The compact&nbsp;<strong>2.3 litre&nbsp;</strong>capacity is enough for 2 people or keeping seconds on standby<br>- Its handy&nbsp;<strong>dial controls</strong>&nbsp;make it easy to select the programs<br>- When you\'re done, it has&nbsp;<strong>dishwasher safe parts</strong> for a quick and easy cleanup</p>', '1776211831_download (1).jpeg', 65.49, 9, 1),
(4, 6, 6, 'ghbnh3', 'ssccfdf', '<p>axdazs cfdzsfc</p>', '1776211867_emtronics-8l-dual-air-fryer-with-timer-grey~5056149883542_01c_MP.webp', 45.99, 0, 2),
(5, 6, 6, 'L80AFB24', 'Air Fryer Dual', '<p>- Cook your favourite meals with this Logik Air Fryer &ndash; enjoy delicious fried chicken, pizza and other treats with&nbsp;<strong>little to no oil</strong><br>- The&nbsp;<strong>two 4-litre</strong>&nbsp;frying baskets mean you can roast your main in one and fry up a side dish in the other<br>- You can&nbsp;grab the baskets safely with the&nbsp;<strong>cool-touch handles</strong>, and the&nbsp;<strong>non-slip feet</strong>&nbsp;keep it steady<br>- With a durable&nbsp;<strong>non-stick coating</strong>, cleaning it\'s a piece of cake &ndash; just wipe everything down</p>', '1776351577_emtronics-8l-dual-air-fryer-with-timer-grey~5056149883542_01c_MP.webp', 79.99, 39, 1),
(18, NULL, NULL, '', 'Tit', '', NULL, 0, 0, 2),
(19, NULL, NULL, '', 'title', '', NULL, 0, 0, 2),
(20, 6, 1, '561201', 'Series 6 MAF671B1GB 9-in-1 Air Fryer & Grill', '<p>- This Bosch air fryer\'s&nbsp;<strong>seven cooking presets</strong>&nbsp;let you rustle up your favourite foods using little to no oil<br>- Cook up to five portions at once in the large<strong>&nbsp;7.2-litre non-stick basket&nbsp;</strong>&ndash; perfect for big families or dinner parties<br>- The&nbsp;<strong>40 - 200&deg;C adjustable thermostat&nbsp;</strong>keeps you in control of your cooking, so you get crispy results each time<br>- It has a&nbsp;<strong>viewing window</strong>&nbsp;and a&nbsp;<strong>light</strong>&nbsp;so you can check on your meal without opening the basket<br>- It\'s super simple to use thanks to the&nbsp;<strong>LED display</strong>&nbsp;and&nbsp;<strong>touch controls</strong><br>- The&nbsp;<strong>dishwasher safe parts</strong> make the post-meal clean up quick and easy</p>', '1778617634_e748ad3108c4.jpg', 129.99, 5, 1),
(21, NULL, NULL, '', 'One more', '', NULL, 0, 0, 2),
(22, 6, NULL, '', 'agtdsr', '', NULL, 0, 0, 2),
(23, 2, NULL, '', 'df', '', NULL, 0, 0, 2),
(24, 12, 1, '245354', 'Silicone TWK7S05GB Jug Kettle', '<p>- This Bosch kettle\'s&nbsp;<strong>Triple Safety feature</strong>&nbsp;protects it from overheating and boiling dry, and automatically shuts off when you lift it off the base<br>- You can open the lid at the push of a button thanks to&nbsp;<strong>one-hand operation<br></strong>- The&nbsp;<strong>heating element is covered</strong>&nbsp;so you won\'t have to descale it nearly as often<br>- The&nbsp;<strong>limescale filter</strong>&nbsp;in the spout can be easily removed when you need to clean it<br>- Tired of cables? The power cable can be&nbsp;<strong>stored in the base</strong> so you can take out only what you need</p>', '1778617321_481e52a445f8.jpg', 45.99, 10, 1),
(25, 25, 6, '724979', 'Jet 60 Turbo Max Cordless Vacuum Cleaner with Jet Fit Brush', '<p><strong>Dust-free home<br><br></strong>Banish dust and dirt. The Samsung&nbsp;<strong>Jet 60 Turbo Max 150 W Suction Power Cordless Vacuum Cleaner with Jet Fit Brush</strong>&nbsp;has a&nbsp;Multi-layered Filtration System that captures up to&nbsp;99.99% of microdust particles, dust and allergens, so that you can breathe really clean air.<br><br>It\'s ideal for allergy sufferers and is a great solution for pet owners.<br><strong><br></strong><strong>Powerful cleaning</strong><strong><br><br></strong>With its 150 W of suction power and airflow-boosting Jet Cyclones, the lightweight&nbsp;<strong>Jet 60</strong>&nbsp;will clean every surface in a flash.<br><br>Its Jet Fit Brush is great for use on hard floors and carpets, so you can clean throughout your home. It also swivels 180 degrees, so you can easily change direction while you\'re cleaning around the house.<br><br>You can detach it from the vacuum with just one click, making it easy to clean it off.<br><br><strong>Washable dust bin</strong><strong><br><br></strong>Cleaning the inside of the&nbsp;<strong>Jet 60</strong>&nbsp;is easy - you can just take the dust bin out and wash it when necessary. There\'s no need to remove the pipe either.<br><br><strong>Long lasting battery</strong><br><br>Offering enough power to run for up to 40 minutes at a time, the&nbsp;<strong>Jet 60</strong><strong>&nbsp;</strong>lets you clean the whole house easily.<br><br>The 2-in-1 charging station can be wall-mounted or used as a standalone charger - you can remove the battery from the vacuum cleaner and pop it in to charge.</p>', '1776947236_bad6939c0654.jpeg', 299, 98, 1),
(26, 6, 5, '', 'sdafsdf', '<p>sa\\fcasdvdv</p>', NULL, 10, 0, 2),
(27, 12, 10, 'FU699949', '7000 Series HD9396/90 Jug Kettle', '<p>This Philips kettle makes it easy to enjoy your favourite drinks at their best. With&nbsp;<strong>six temperatures from 40&deg;C to 100&deg;C</strong>, you\'ll always get the perfect heat for tea, coffee or instant soups. The&nbsp;<strong>double-walled design</strong>&nbsp;keeps the exterior cool to the touch while boiling fast and efficiently. And with a&nbsp;<strong>1.7 litre capacity</strong>, you can boil enough water for the whole family in one go.<br><br><strong>Good to know<br><br></strong>- The&nbsp;<strong>spring-release lid</strong>&nbsp;makes refilling quick and fuss free<br>- Its&nbsp;<strong>cup indicator</strong>&nbsp;helps you heat just the amount you need<br><strong>-&nbsp;</strong>The&nbsp;<strong>heating element&nbsp;is concealed</strong>&nbsp;so you won\'t have to descale it nearly as often<br>- Its&nbsp;<strong>360&deg; pirouette base</strong>&nbsp;means you can pop it back down from any angle<br>- The&nbsp;<strong>handle display</strong> lets you check your chosen temperature at a glance</p>', '1777541756_333e49dabee0.jpeg', 69.99, 9, 1),
(28, 30, 1, '5056709537168', 'AdvancedImpactDrive', '<p>Compact powerhouse - shortest head length in its class Compact design with 155 mm head length for work in confined areas 130 Nm of powerful torque for tough projects Fast results via impact rate of 0-3,200 per min Syneon Technology regulates energy use for optimal efficiency and longer runtime POWER FOR ALL: One battery and charger for an entire Home Garden tools system Convenience and power - the AdvancedImpactDrive 18 makes tightening bolts simple thanks to an ergonomic shape and lightweight design for easy use in demanding jobs. Tough work made simple - the easy-to-use cordless PDR 18 LI features a compact head length of 155 mm for screwdriving, drilling, and bolting projects in confined areas. Its 130 Nm of powerful torque tackles tough jobs in a flash thanks to a high impact rate of 0-3,200 per min. This impact wrench is perfect for screwdriving or bolting in wood (soft, hard) and metal as well as drilling in wood (soft, hard), metal, mortar, and brick. The tool belongs to the POWER FOR ALL ALLIANCE. One battery can operate a large variety of tools from different brands.</p>', '1778619246_61410183caa2.jpg', 79.99, 5, 1),
(29, 29, 1, '4053423229912', '18V Power for all Cordless Jigsaw', '<h3 class=\"mb-md pt-lg first:pt-0\">Product information</h3>\r\n<p class=\"mb-md whitespace-break-spaces\">Easy-to-use and lightweight jigsaw for all basic applications.</p>\r\n<ul class=\"list-disc pl-md\">\r\n<li class=\"whitespace-break-spaces\">Comes with 1 x saw blade T 144 D</li>\r\n<li class=\"whitespace-break-spaces\">Battery not included</li>\r\n</ul>\r\n<h3 class=\"mb-md pt-lg first:pt-0\">Features and benefits</h3>\r\n<ul class=\"list-disc pl-md\">\r\n<li class=\"whitespace-break-spaces\">100 mm cutting depth and multiple accessories for convincing results</li>\r\n<li class=\"whitespace-break-spaces\">Improved and even more intuitive easy-to-use SDS enables quick and easy keyless saw blade changes</li>\r\n<li class=\"whitespace-break-spaces\">Speed trigger for comfortable use, with a soft start and power adjustment</li>\r\n<li class=\"whitespace-break-spaces\">Adjustable footplate allows angle cuts of up to 45&deg; into wooden work-tops, parquet flooring and aluminium profiles</li>\r\n<li class=\"whitespace-break-spaces\">Improved and even more intuitive easy-to-use SDS enables quick and easy keyless saw blade changes</li>\r\n<li class=\"whitespace-break-spaces\">Part of the 18V Power For All System. One hundred tools can now be operated with a Bosch 18V Power for All rechargeable battery. Whether that&rsquo;s drilling, cleaning or gardening, switch quickly and easily between different devices.</li>\r\n<li class=\"whitespace-break-spaces\">Guarantee can be extended from 2 to 3 years via registration within 28 days on MyBosch.</li>\r\n</ul>', '1778619501_c0d08dda8987.jpg', 70, 8, 1),
(30, 29, 3, 'DJV180Z', '18V LXT Brushed Cordless Jigsaw', '<p class=\"mb-md whitespace-break-spaces\">The Makita DJV180Z is a compact and lightweight jigsaw powered by Makita lithium-ion batteries. This 18 volt LXT lithium-ion cordless jigsaw with tool-less change and built in job light comes as body only. This lightweight and compact Jigsaw has soft start and is double insulated. It offers smooth and powerful cutting at the high rotational speed at 2,600 spm.</p>\r\n<ul class=\"list-disc pl-md\">\r\n<li class=\"whitespace-break-spaces\">With LED work light</li>\r\n<li class=\"whitespace-break-spaces\">Battery not included</li>\r\n</ul>\r\n<h3 class=\"mb-md pt-lg first:pt-0\">Features and benefits</h3>\r\n<ul class=\"list-disc pl-md\">\r\n<li class=\"whitespace-break-spaces\">Large 2 finger trigger</li>\r\n<li class=\"whitespace-break-spaces\">Blower clears debris from cut line</li>\r\n<li class=\"whitespace-break-spaces\">Tool-less blade change</li>\r\n<li class=\"whitespace-break-spaces\">LED job light that creates and easy to trace cutting line</li>\r\n<li class=\"whitespace-break-spaces\">Guarantee - 1 year</li>\r\n</ul>', '1778619648_7f8e9bc9efef.jpg', 150, 7, 1),
(31, 28, 3, 'DHP484Z', '18V LXT Lithium Ion Brushless Combi Hammer Drill', '<p>Model DHP484 is a Cordless Hammer Driver Drill powered by 18V Li-ion battery (sold separate). Featuring the Makita Brushless motor, precision engineered to be up to 50% more efficient than a comparative model. Without brushes in place to apply friction a Brushless motor can produce a greater measure of torque per weight. With more torque at your disposal less power is required for day to day use, adding to the motors efficiency and extending the products overall lifetime as maintenance becomes less of an issue. The DHP484 has been developed to succeed model DHP480. Features and Benefits: Brushless motor Electric brake 2 mechanical gears. Variable speed control by trigger Forward/reverse rotation LED job light Compact Overall Length of 182mm Keyless Chuck Aluminium Gear Housing All metal gear construction Ergonomic soft grip Belt clip</p>', '1778619729_0fae1bca7b09.jpg', 93.99, 15, 1),
(32, 31, 1, '1600A034GL', 'Engineer\'s Hammer 300 g + soft bumper', '<p>Comfortable, high-quality engineer\'s hammer with smooth striking power Peak striking performance from high-quality tool composition carbon steel Fibreglass core ensures top striking efficiency with fewer vibrations Ergonomic design with tool holding guidance for increased control and comfort More working comfort thanks to balanced weight Optimal force and tactile control thanks to ergonomic handle with soft grips This high-quality engineer\'s hammer makes light work of DIY construction or carpentry. Made of superior materials, including fibreglass, this tool delivers high-performance force, top control and ergonomic comfort. For controlled striking force and comfort, the Bosch engineer\'s hammer belongs in every DIY toolbox. This is a robust, high-quality hammer with a carbon steel head. Weight is balanced in this tool, and it has a fibreglass core so less vibrations and more striking efficiency. This hammer is built to last and built for ergonomic comfort: alongside superior composition, this tool has a two-component handle with large soft grip areas for better handling and highly tactile control.</p>', '1778620652_e21e1c0c71db.jpg', 15.99, 14, 1),
(33, 31, 1, '1600A02ZA4', 'Hand Tools Club Hammer', '<p class=\"mb-md whitespace-break-spaces\">Powerful 1000g club hammer for heavy-duty tasks Powerful hammering with high-quality carbon steel head Top striking efficiency and fewer vibrations thanks to fiberglass core Easy handling and striking via symmetric 1000g head design Optimal force and control thanks to ergonomic softgrip handle Ideal for renovation and chiseling According to standards: ISO 15601; DIN 1193 The Bosch Club Hammer 1000g is the powerful hammer of choice for strength demanding DIY renovation and chiseling tasks. It has a high-quality carbon steel composition, a fiberglass core and a symmetric head, making it easy to use, efficient and powerful. For big DIY hammering jobs where strength is a must, the Bosch Club Hammer 1000g should be in every toolbox. Composed of high-quality carbon steel (50-58 HRC), this hammer delivers powerful blows. It has a fiberglass core, so it strikes more efficiently while generating fewer vibrations. Designed for easy handling and striking thanks to the symmetric 1000g head design. When hitting a chisel, the ergonomic softgrip handle is an extra benefit that enhances tactile control and comfort for optimal force and ease of use.</p>', '1778620879_39848fa8b514.jpg', 25.45, 8, 1),
(34, 25, 14, '691618', 'RCV 3 Robot Vacuum Cleaner', '<p>-&nbsp;The&nbsp;<strong>precise mapping</strong>&nbsp;feature lets you store multiple maps&nbsp;of your rooms and set no-go zones<strong><br>-&nbsp;</strong>Don\'t worry about those stairs and ledges thanks to the&nbsp;<strong>fall sensor -&nbsp;</strong>it\'ll always know to avoid those drops<br>- From its current status to service reminders, the&nbsp;<strong>voice output</strong>&nbsp;will make sure to tell you<br>- Set a cleaning schedule for anytime of the day or the week with the&nbsp;<strong>timer program</strong></p>', '1778621728_2fa3477835bd.jpg', 172.95, 10, 1),
(35, 6, NULL, '', 'some', '<p>some descriptions</p>', NULL, 0, 0, 2),
(36, 12, NULL, 'rdthggf', 'dsdg', '<p>asfzdhgdsfh</p>', NULL, 58, 1, 2),
(37, 31, NULL, '5063973130674', 'Thor Thorex Nylon Hammer White/Black', '<p>Material: Chrome Plated, Nylon. Plastic Handle. Design: Contrast, Logo. Multi-Purpose.</p>', '1782245229_41e07f2ded80.jpg', 40, 5, 1),
(38, 35, 1, 'TAT2M123GB', 'MyMoment Delight 2 Slice Toaster', '<p>Enjoy browned toast every morning with the Bosch MyMoment Delight 2 Slice Toaster. Its 6 toasting settings let you achieve your preferred level of crispness, while the high-lift function makes it easy to retrieve smaller bread slices without burning your fingers. The handy defrost setting toasts bread straight from the freezer, a convenient feature for busy mornings.</p>\r\n<p>The integrated bun warming rack heats croissants and rolls, with a reheat function that warms toast without browning. Controls and crumb tray are at the front for easy access. The matt black body with glossy accents adds a contemporary touch, backed by a 2-year guarantee.</p>', '1782314625_3428c2e36bf2.png', 35, 10, 1);

-- --------------------------------------------------------

--
-- Table structure for table `product_variation`
--

CREATE TABLE `product_variation` (
  `id` int(11) NOT NULL,
  `productID` int(11) NOT NULL,
  `attributeID` int(11) NOT NULL,
  `valueID` int(11) NOT NULL,
  `priceOverride` double DEFAULT NULL,
  `sku` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_variation`
--

INSERT INTO `product_variation` (`id`, `productID`, `attributeID`, `valueID`, `priceOverride`, `sku`) VALUES
(1, 1, 13, 21, NULL, 'b3ai700'),
(2, 1, 13, 22, 150, 'b3ai800'),
(3, 2, 13, 21, NULL, 'b3aig700'),
(4, 2, 13, 22, 65, 'b3aig800'),
(24, 22, 13, 20, 0, ''),
(25, 22, 13, 21, 0, ''),
(26, 22, 13, 22, 0, ''),
(64, 20, 10, 8, NULL, '561201W'),
(65, 20, 10, 30, NULL, '561201B'),
(66, 24, 10, 1, NULL, '245354R'),
(67, 24, 10, 36, NULL, '245354G'),
(68, 36, 13, 20, NULL, ''),
(73, 27, 10, 1, NULL, ''),
(74, 27, 10, 30, 59.99, ''),
(75, 38, 10, 30, 35, ''),
(76, 38, 10, 8, 40, '');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `productID` int(11) NOT NULL,
  `orderID` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL,
  `comment` text DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `userID`, `productID`, `orderID`, `rating`, `comment`, `status`, `created_at`) VALUES
(2, 3, 27, 2, 5, 'Philips kettle is a good design,,,,, you can set the kettle for different boil temperatures,,,, it looks great,,, the only fault is size it is a very large kettle and heavy when full with water,,,, looks classy.', 1, '2026-05-11 21:06:54'),
(6, 3, 20, 4, 5, '', 1, '2026-05-11 23:39:54'),
(7, 4, 25, 5, 5, '', 1, '2026-05-13 20:59:38'),
(8, 4, 29, 7, 4, '', 2, '2026-05-22 10:17:53'),
(9, 5, 27, 8, 5, '', 1, '2026-06-11 11:37:42'),
(10, 6, 27, 9, 5, '', 1, '2026-06-20 00:44:07'),
(11, 9, 32, 13, 5, 'Nice hammer', 2, '2026-06-23 19:47:53'),
(17, 10, 27, 14, 5, '', 1, '2026-06-24 15:29:32');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `surname` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `tel` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `surname`, `email`, `tel`, `password`, `role`) VALUES
(1, 'Lana', 'Malevich', 'lana@gmail.com', NULL, '$2y$10$Vu85TFapnQzz5W0FDePAUefMz4UY5ZUiiqHsRmPJRIesl6vz37ndu', 1),
(3, 'Mulana', 'Mars', 'mulana@gmail.com', '', '$2y$10$wnqGdu3aTnBvgE/GFRY67ey8xMPpKA1OymQCqDwOr4sHhVSB06b7u', 0),
(4, 'John', 'Li', 'john.li@gmail.com', '07456589', '$2y$10$fHkT2k8xUxHLeOpx030z5OpJpru.nTcJFrUg2FQx7k53c5S3lPmo6', 0),
(5, 'ksddsf', 'vbghmjvgbkm', 'lana@workstuffuk.com', '', '$2y$10$X80XcsB86skmbYWr5D3E.e/fI.bNIgs6vzOgXc.rprTydc1HCoQzW', 0),
(6, 'Kira', 'Malevich', 'kira@gmail.com', '', '$2y$10$1z2PHWQkxfL2Hknwxh9xN.0nvi1j/HUMsCLY7PSsbrgYg9c8YwW66', 0),
(7, 'Parmy', 'Munder', 'munder@gmail.com', '', '$2y$10$uOi4O1kcjLWOxrB/86uSmOA.af7zYVH9/O7ZT/IlthFwCX17SOYcS', 0),
(8, 'Neil', 'Juego', 'juego@gmail.com', '', '$2y$10$BblzY46RCQNtV/MNLwKYOuNabXyX1eHx19m1.1cgqSQojhHrlkBfq', 0),
(9, 'Zuhra', 'Amazon', 'zuhra@gmail.com', '', '$2y$10$sLBZUxmBZW2BcjqoXg9Xt.0wzFXuYdUojYlH/0neWk4zY7jN3JMcq', 0),
(10, 'Gracy', 'Turner', 'gracy@gmail.com', '', '$2y$10$NbDAI5sRhhbPa.vCn22ROu80fk8feNMNLyawVtbE2pmjEPE4MYeRe', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `userID` (`userID`);

--
-- Indexes for table `attributes`
--
ALTER TABLE `attributes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `attributes_category`
--
ALTER TABLE `attributes_category`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attributeID` (`attributeID`),
  ADD KEY `categoryID` (`categoryID`);

--
-- Indexes for table `attributes_product`
--
ALTER TABLE `attributes_product`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attributeID` (`attributeID`),
  ADD KEY `productID` (`productID`),
  ADD KEY `valueID` (`valueID`);

--
-- Indexes for table `attribute_values`
--
ALTER TABLE `attribute_values`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attribute_id` (`attributeID`);

--
-- Indexes for table `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `delivery`
--
ALTER TABLE `delivery`
  ADD PRIMARY KEY (`id`),
  ADD KEY `addressID` (`addressID`),
  ADD KEY `methodID` (`methodID`),
  ADD KEY `orderID` (`orderID`);

--
-- Indexes for table `delivery_method`
--
ALTER TABLE `delivery_method`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `deliveryID` (`deliveryID`),
  ADD KEY `userID` (`userID`),
  ADD KEY `peymentMethodID` (`peymentMethodID`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `orderID` (`orderID`),
  ADD KEY `productID` (`productID`);

--
-- Indexes for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `brandID` (`brandID`),
  ADD KEY `categoryID` (`categoryID`);

--
-- Indexes for table `product_variation`
--
ALTER TABLE `product_variation`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attributeID` (`attributeID`),
  ADD KEY `productID` (`productID`),
  ADD KEY `valueID` (`valueID`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_review` (`userID`,`productID`),
  ADD KEY `productID` (`productID`),
  ADD KEY `orderID` (`orderID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addresses`
--
ALTER TABLE `addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `attributes`
--
ALTER TABLE `attributes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `attributes_category`
--
ALTER TABLE `attributes_category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

--
-- AUTO_INCREMENT for table `attributes_product`
--
ALTER TABLE `attributes_product`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=198;

--
-- AUTO_INCREMENT for table `attribute_values`
--
ALTER TABLE `attribute_values`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `brands`
--
ALTER TABLE `brands`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `delivery`
--
ALTER TABLE `delivery`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `delivery_method`
--
ALTER TABLE `delivery_method`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `product_variation`
--
ALTER TABLE `product_variation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `addresses_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `users` (`id`);

--
-- Constraints for table `attributes_category`
--
ALTER TABLE `attributes_category`
  ADD CONSTRAINT `attributes_category_ibfk_1` FOREIGN KEY (`attributeID`) REFERENCES `attributes` (`id`),
  ADD CONSTRAINT `attributes_category_ibfk_2` FOREIGN KEY (`categoryID`) REFERENCES `categories` (`id`);

--
-- Constraints for table `attributes_product`
--
ALTER TABLE `attributes_product`
  ADD CONSTRAINT `attributes_product_ibfk_1` FOREIGN KEY (`attributeID`) REFERENCES `attributes` (`id`),
  ADD CONSTRAINT `attributes_product_ibfk_2` FOREIGN KEY (`productID`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `attributes_product_ibfk_3` FOREIGN KEY (`valueID`) REFERENCES `attribute_values` (`id`);

--
-- Constraints for table `attribute_values`
--
ALTER TABLE `attribute_values`
  ADD CONSTRAINT `attribute_values_ibfk_1` FOREIGN KEY (`attributeID`) REFERENCES `attributes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `delivery`
--
ALTER TABLE `delivery`
  ADD CONSTRAINT `delivery_ibfk_1` FOREIGN KEY (`addressID`) REFERENCES `addresses` (`id`),
  ADD CONSTRAINT `delivery_ibfk_2` FOREIGN KEY (`methodID`) REFERENCES `delivery_method` (`id`),
  ADD CONSTRAINT `delivery_ibfk_3` FOREIGN KEY (`orderID`) REFERENCES `orders` (`id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`deliveryID`) REFERENCES `delivery` (`id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`userID`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`peymentMethodID`) REFERENCES `payment_methods` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`orderID`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`productID`) REFERENCES `products` (`id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`brandID`) REFERENCES `brands` (`id`),
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`categoryID`) REFERENCES `categories` (`id`);

--
-- Constraints for table `product_variation`
--
ALTER TABLE `product_variation`
  ADD CONSTRAINT `product_variation_ibfk_1` FOREIGN KEY (`attributeID`) REFERENCES `attributes` (`id`),
  ADD CONSTRAINT `product_variation_ibfk_2` FOREIGN KEY (`productID`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `product_variation_ibfk_3` FOREIGN KEY (`valueID`) REFERENCES `attribute_values` (`id`);

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`productID`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`orderID`) REFERENCES `orders` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
