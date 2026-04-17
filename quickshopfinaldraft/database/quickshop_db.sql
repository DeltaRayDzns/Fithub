-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 06, 2025 at 11:32 AM
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
-- Database: `quickshop_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(100) NOT NULL,
  `user_id` int(100) NOT NULL,
  `pid` int(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` int(100) NOT NULL,
  `quantity` int(100) NOT NULL,
  `image` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `pid`, `name`, `price`, `quantity`, `image`) VALUES
(59, 37, 25, 'Lettuce (300g)', 130, 2, 'lettuce.png'),
(65, 40, 27, 'Fresh Tilapia (1kg)', 220, 1, 'Fresh Tilapia.png'),
(66, 40, 25, 'Lettuce (300g)', 130, 1, 'lettuce.png'),
(69, 42, 25, 'Lettuce (300g)', 130, 1, 'lettuce.png'),
(70, 42, 24, 'Banana (1kg)', 80, 1, 'banana.png'),
(89, 46, 111, 'Ginger (1kg)', 214, 1, 'ginger.png'),
(90, 46, 112, 'Ampalaya/Bittermelon (1Kg)', 90, 11, 'pngtree-bitter-gourd-png-image_2451202-removebg-preview.png'),
(91, 46, 107, 'Tomato (1Kg)', 65, 1, 'tomato-clip-art-tomato-png-clipart-c96fd0e259c5116b6afd209901b82085.png'),
(92, 46, 103, 'Tortilla (10 Pcs)', 130, 1, '7l9n4kvufk0i0mbr6vcq7uo4jv.png'),
(93, 46, 110, 'Pechay (1kg)', 148, 1, 'pechay.png'),
(115, 49, 112, 'Ampalaya/Bittermelon (1Kg)', 90, 1, 'pngtree-bitter-gourd-png-image_2451202-removebg-preview.png'),
(116, 49, 111, 'Ginger (1kg)', 214, 1, 'ginger.png'),
(117, 49, 110, 'Pechay (1kg)', 148, 1, 'pechay.png'),
(118, 49, 107, 'Tomato (1Kg)', 65, 1, 'tomato-clip-art-tomato-png-clipart-c96fd0e259c5116b6afd209901b82085.png');

-- --------------------------------------------------------

--
-- Table structure for table `message`
--

CREATE TABLE `message` (
  `id` int(100) NOT NULL,
  `user_id` int(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `number` varchar(12) NOT NULL,
  `message` varchar(500) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `status` varchar(20) NOT NULL DEFAULT 'unread'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `message`
--

INSERT INTO `message` (`id`, `user_id`, `name`, `email`, `number`, `message`, `created_at`, `status`) VALUES
(13, 43, 'Jennard', 'jeadkso@gmail', '120937123712', 'asdawdadw', '2025-10-18 11:20:35', 'read'),
(14, 43, 'Jennard', 'jeadkso@gmail', '120937123712', '42rqawdad', '2025-10-18 11:31:37', 'read'),
(15, 43, 'Jennard', 'jeadkso@gmail', '120937123712', 'wqdwqwdqdqs', '2025-10-18 11:31:56', 'read'),
(16, 43, 'Jennard', 'jeadkso@gmail', '120937123712', 'asdasdasd', '2025-10-18 11:40:07', 'read'),
(17, 43, 'Jennard', 'jeadkso@gmail', '120937123712', 'asdasdasdasd', '2025-10-18 11:42:41', 'read'),
(18, 43, 'Jennard', 'jeadkso@gmail', '120937123712', 'sasdadsadd', '2025-10-18 11:43:17', 'read'),
(19, 43, 'Jennard', 'jeadkso@gmail', '120937123712', 'sadawawdasddaw', '2025-10-18 11:46:37', 'read'),
(20, 43, 'Jennard', 'jeadkso@gmail', '120937123712', 'asdwdadadsasw', '2025-10-18 11:48:34', 'read'),
(21, 43, 'Jennard', 'jeadkso@gmail', '120937123712', 'asdwadwadssadwa', '2025-10-18 11:50:07', 'read'),
(22, 43, 'Jennard', 'jeadkso@gmail', '120937123712', 'adwadsdawdadaw', '2025-10-18 11:51:45', 'read'),
(23, 43, 'Jennard', 'jeadkso@gmail', '120937123712', 'asasaasdadxczxcac', '2025-10-18 11:52:19', 'read'),
(24, 43, 'Jennard', 'jeadkso@gmail', '120937123712', 'asdawdasdas', '2025-10-18 11:54:10', 'read'),
(25, 43, 'Jennard', 'jeadkso@gmail', '120937123712', 'asdwasda', '2025-10-18 11:56:49', 'read'),
(26, 43, 'Jennard', 'jeadkso@gmail', '120937123712', 'asdadas', '2025-10-18 12:05:50', 'read'),
(27, 43, 'Jennard', 'jeadkso@gmail', '120937123712', 'asdawdasdaw', '2025-10-18 12:07:20', 'read'),
(28, 43, 'Jennard', 'jeadkso@gmail', '120937123712', 'asdwads', '2025-10-18 15:34:22', 'unread');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(100) NOT NULL,
  `user_id` int(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `number` varchar(12) NOT NULL,
  `email` varchar(100) NOT NULL,
  `method` varchar(50) NOT NULL,
  `address` varchar(500) NOT NULL,
  `total_products` varchar(1000) NOT NULL,
  `total_price` int(100) NOT NULL,
  `placed_on` datetime(6) NOT NULL,
  `payment_status` varchar(20) NOT NULL DEFAULT 'pending',
  `cancel_reason` text DEFAULT NULL,
  `cancel_date` datetime DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `discount_type` varchar(50) NOT NULL DEFAULT 'None',
  `id_image_proof` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `name`, `number`, `email`, `method`, `address`, `total_products`, `total_price`, `placed_on`, `payment_status`, `cancel_reason`, `cancel_date`, `image`, `discount_type`, `id_image_proof`) VALUES
(18, 43, 'Jennard', '131231231231', 'jennard@gmail.com', 'cash on delivery', 'Rizal, Antipolo City, Mayamot, 4', 'Fresh Tilapia (1kg) ( 1 )', 220, '2025-10-17 09:30:00.000000', 'completed', NULL, NULL, NULL, 'None', NULL),
(19, 43, 'Jennard', '131231231231', 'jennard@gmail.com', 'cash on delivery', 'Rizal, Antipolo City, Mayamot, 4', 'Lettuce (300g) ( 1 ), Banana (1kg) ( 1 ), Chicken (1kg) ( 1 )', 510, '2025-10-17 09:52:15.000000', 'completed', NULL, NULL, NULL, 'None', NULL),
(20, 1, 'Karl', '09123456789', 'karl@g.com', 'cash on delivery', 'Region, Province, City, Barangay, Street', 'Sample Product (1)', 1500, '2025-10-23 22:17:32.000000', 'completed', NULL, NULL, NULL, 'None', NULL),
(21, 48, 'Xtian Joshua Lacerna', '09123456789', 'xtianjoshual@gmail.com', 'cash on delivery', 'Region IV-A (CALABARZON), Rizal, City of Antipolo , Dela Paz , 100', 'Ginger (1kg) ( 1 ), Red Onion (!Kg) ( 2 ), Ground Beef (1kg) ( 1 ), CDO Tocino (450 g) ( 1 )', 914, '2025-10-23 16:29:20.000000', 'completed', NULL, NULL, NULL, 'None', NULL),
(22, 48, 'Xtian Joshua Lacerna', '09123456789', 'xtianjoshual@gmail.com', 'GCash', 'Region IV-A (CALABARZON), Rizal, City of Antipolo , Dela Paz , 100', 'Ginger (1kg) ( 1 ), Ampalaya/Bittermelon (1Kg) ( 1 ), Pechay (1kg) ( 1 )', 362, '2025-10-23 16:34:39.000000', 'completed', NULL, NULL, 'gcash/1761230079_gcash_referencenumber.jpg', 'Senior', 'id_uploads/1761230079_id_reeses.png'),
(23, 49, 'Karl Alalay Pogi', '09123456789', 'karlpogi@gmail.com', 'GCash', 'National Capital Region (NCR), , City of Marikina, Barangka, 100', 'Pechay (1kg) ( 1 ), Red Onion (!Kg) ( 2 ), Ground Beef (1kg) ( 1 ), Tortilla (10 Pcs) ( 1 )', 838, '2025-10-23 17:17:45.000000', 'completed', NULL, NULL, 'gcash/1761232665_gcash_watermelon.png', 'PWD', 'id_uploads/1761232665_id_ginger.png'),
(24, 49, 'Karl Alalay Pogi', '09123456789', 'karlpogi@gmail.com', 'GCash', 'National Capital Region (NCR), , City of Marikina, Barangka, 100', 'Ginger (1kg) ( 1 ), Tomato (1Kg) ( 1 ), Tortilla (10 Pcs) ( 1 )', 409, '2025-10-23 17:28:28.000000', 'completed', NULL, NULL, 'gcash/1761233308_gcash_reeses.png', 'PWD', 'id_uploads/1761233308_id_kisses.png'),
(25, 34, 'Christian Lacerna', '', 'cjlacerna@gmail.com', 'cash on delivery', ', , , , ', 'Pechay (1kg) ( 1 ), Tomato (1Kg) ( 1 ), Red Onion (!Kg) ( 1 ), Ampalaya/Bittermelon (1Kg) ( 1 )', 413, '2025-11-03 07:47:08.000000', 'completed', NULL, NULL, NULL, 'None', NULL),
(26, 50, 'ChristianJ Lacerna', '09123456789', 'cjlacerna6@gmail.com', 'cash on delivery', 'Region IV-A (CALABARZON), Rizal, City of Antipolo , Dela Paz , 99', 'Ginger (1kg) ( 1 )', 214, '2025-11-03 07:54:03.000000', 'completed', NULL, NULL, NULL, 'None', NULL),
(27, 50, 'ChristianJ Lacerna', '09123456789', 'cjlacerna6@gmail.com', 'cash on delivery', 'Region IV-A (CALABARZON), Rizal, City of Antipolo , Dela Paz , 99', 'Pechay (1kg) ( 1 )', 148, '2025-11-03 07:59:08.000000', 'completed', NULL, NULL, NULL, 'None', NULL),
(28, 50, 'ChristianJ Lacerna', '09123456789', 'cjlacerna6@gmail.com', 'cash on delivery', 'Region IV-A (CALABARZON), Rizal, City of Antipolo , Dela Paz , 99', 'Pechay (1kg) ( 1 ), Ginger (1kg) ( 1 )', 362, '2025-11-03 08:05:42.000000', 'completed', NULL, NULL, NULL, 'None', NULL),
(29, 50, 'ChristianJ Lacerna', '09123456789', 'cjlacerna6@gmail.com', 'cash on delivery', 'Region IV-A (CALABARZON), Rizal, City of Antipolo , Dela Paz , 99', 'CDO Tocino (450 g) ( 1 )', 140, '2025-11-03 08:09:06.000000', 'completed', NULL, NULL, NULL, 'None', NULL),
(30, 50, 'ChristianJ Lacerna', '09123456789', 'cjlacerna6@gmail.com', 'cash on delivery', 'Region IV-A (CALABARZON), Rizal, City of Antipolo , Dela Paz , 99', 'Ground Pork (1kg) ( 1 )', 297, '2025-11-03 08:14:58.000000', 'completed', NULL, NULL, NULL, 'None', NULL),
(31, 50, 'ChristianJ Lacerna', '09123456789', 'cjlacerna6@gmail.com', 'cash on delivery', 'Region IV-A (CALABARZON), Rizal, City of Antipolo , Dela Paz , 99', 'Tomato (1Kg) ( 1 )', 65, '2025-11-03 08:32:19.000000', 'cancelled', 'I changed my mind, sorry.', '2025-11-03 15:38:54', NULL, 'None', NULL),
(32, 50, 'ChristianJ Lacerna', '09123456789', 'cjlacerna6@gmail.com', 'cash on delivery', 'Region IV-A (CALABARZON), Rizal, City of Antipolo , Dela Paz , 99', 'Ground Pork (1kg) ( 1 )', 297, '2025-11-03 09:22:46.000000', 'pending', NULL, NULL, NULL, 'None', NULL),
(33, 50, 'ChristianJ Lacerna', '09123456789', 'cjlacerna6@gmail.com', 'cash on delivery', 'Region IV-A (CALABARZON), Rizal, City of Antipolo , Dela Paz , 99', 'Pechay (1kg) ( 1 )', 148, '2025-11-03 09:24:48.000000', 'completed', NULL, NULL, NULL, 'None', NULL),
(34, 50, 'ChristianJ Lacerna', '09123456789', 'cjlacerna6@gmail.com', 'GCash', 'Region IV-A (CALABARZON), Rizal, City of Antipolo , Dela Paz , 99', 'Ginger (1kg) ( 1 ), Pechay (1kg) ( 1 ), Kale (Bunch) ( 1 ), Tomato (1Kg) ( 1 )', 627, '2025-11-06 11:13:05.000000', 'pending', NULL, NULL, 'gcash/1762423985_gcash_referencenumber.jpg', 'PWD', 'id_uploads/1762423985_id_dog.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `philippine_addresses`
--

CREATE TABLE `philippine_addresses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `region` varchar(100) NOT NULL,
  `province` varchar(100) NOT NULL,
  `city` varchar(100) NOT NULL,
  `barangay` varchar(100) NOT NULL,
  `street` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `philippine_addresses`
--

INSERT INTO `philippine_addresses` (`id`, `user_id`, `region`, `province`, `city`, `barangay`, `street`) VALUES
(1, 44, 'Region IV-A (CALABARZON)', 'Rizal', 'City of Antipolo ', 'Dela Paz ', '100');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `category` varchar(20) NOT NULL,
  `details` varchar(500) NOT NULL,
  `price` int(100) NOT NULL,
  `image` varchar(100) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 10
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `category`, `details`, `price`, `image`, `stock`) VALUES
(24, 'Banana (1kg)', 'fruits', 'A healthy, delicious staple: &#34;Sweet and creamy, our bananas are a perfect on-the-go snack, rich in potassium and a natural source of energy&#34;.', 80, 'banana.png', 0),
(25, 'Lettuce (300g)', 'vegetables', 'Lettuce is a leafy green vegetable (scientific name Lactuca sativa) from the Asteraceae family, grown for its edible leaves, which are crisp, tender, and often slightly sweet. It&#39;s a versatile ingredient, forming the base of many salads and used in sandwiches and wraps. Common types include head lettuce (like crisphead/iceberg) and leaf lettuce (such as romaine and loose-leaf varieties), varying in color, shape, and leaf texture.  ', 130, 'lettuce.png', 0),
(26, 'Chicken (1kg)', 'meat', 'A tender, juicy, and versatile choice for any meal. Perfect for roasting, grilling, or slow cooking, it&#39;s a great option for family dinners or meal prep. Its rich flavor and moist meat make it a delicious and satisfying centerpiece for your next culinary creation.', 300, 'chicken.png', 0),
(27, 'Tilapia (1kg)', 'fish', 'A mild, white fish with a delicate flavor and a firm, flaky texture. It&#39;s an excellent and healthy source of protein, making it a great choice for a light and flavorful meal. This versatile fish is delicious baked, pan-seared, or grilled.', 220, 'Fresh Tilapia.png', 35),
(28, 'Loaf Bread', 'bread', 'A loaf of bread is a single, shaped unit of baked bread, typically oblong or rounded in form, and usually sliced for eating. It&#39;s made from a dough of flour, water, and yeast or leavening, baked in a pan or on a sheet, and is a staple food used for sandwiches, toast, and other culinary purposes.  ', 160, 'LoafBread.png', 9),
(29, 'Apple (500g)', 'fruits', 'A crisp, sweet, and juicy fruit known for its refreshing flavor and vibrant red color. Apples are rich in fiber, vitamins, and antioxidants, making them a healthy snack choice. Perfect for eating fresh, adding to salads, or using in desserts and juices.\r\n', 143, 'apple.png', 10),
(30, 'Kiwi (250g)', 'fruits', 'A small, fuzzy fruit with bright green flesh and a sweet-tart flavor. Kiwis are packed with vitamin C, fiber, and antioxidants that support a healthy immune system. Perfect for eating fresh, topping desserts, or blending into smoothies for a tropical twist.', 127, 'kiwi.png', 10),
(31, 'Grape (500g)', 'fruits', 'Small, sweet, and juicy fruits that come in a variety of colors like green, red, and purple. Grapes are rich in vitamins and antioxidants, making them a healthy and refreshing snack. Perfect for eating fresh, mixing into fruit salads, or making into juice and wine.', 300, 'grapes.png', 9),
(32, 'Strawberry (350g)', 'fruits', 'Bright red, juicy, and naturally sweet, strawberries are a favorite for their fresh flavor and aroma. Rich in vitamin C and antioxidants, theyâ€™re perfect for eating fresh, adding to desserts, or blending into smoothies and juices.', 250, 'berry.png', 12),
(34, 'Mango (500g)', 'fruits', 'A tropical fruit known for its rich sweetness, vibrant golden color, and smooth texture. Mangoes are packed with vitamins A and C, making them both delicious and nutritious. Perfect for eating fresh, blending into smoothies, or adding to desserts and salads.', 104, 'mango.png', 10),
(35, 'Watermelon (1.5kg)', 'fruits', 'A refreshing and hydrating fruit with a sweet, juicy taste and vibrant red flesh. Watermelon is rich in vitamins A and C and perfect for cooling down on a hot day. Enjoy it chilled, in fruit salads, or blended into a delicious smoothie.\r\n', 303, 'watermelon.png', 8),
(36, 'Orange (500g)', 'fruits', 'A bright, juicy fruit bursting with sweet and tangy flavor. Oranges are packed with vitamin C and antioxidants that help boost your immune system. Perfect for snacking, juicing, or adding a refreshing citrus twist to your favorite dishes and desserts.\r\n', 100, 'erasebg-transformed.png', 10),
(37, 'Pear (250g)', 'fruits', 'A soft and juicy fruit with a delicate sweetness and smooth texture. Pears are rich in fiber, vitamins, and antioxidants, making them a nutritious and refreshing choice. Perfect for eating fresh, adding to salads, or using in desserts and baked dishes.', 110, 'pear.png', 20),
(39, 'Dairy Milk (165g)', 'dairies', 'Dairy milk is a nutrient-rich beverage derived from cows and other dairy animals, providing a smooth, creamy liquid thatâ€™s naturally high in calcium, vitamin D, and protein. Itâ€™s widely consumed on its own, poured over cereals, or used as a base in coffee, smoothies, and cooking. Common variants include whole milk (about 3.5% fat), reduced-fat (2% fat), low-fat (1% fat), and skim/milk with minimal fat, each offering a distinct mouthfeel and flavor profile. Lactose content and flavor can vary', 155, '634d2a172304b002065a60e3-cadbury-dairy-milk-200-g-removebg-preview.png', 10),
(40, 'Whole Milk Gallon', 'dairies', 'Premium dairy staple with about 3.25â€“3.5% fat for a rich, creamy taste. Packaged in a sturdy gallon for easy pouring. Contains calcium, vitamin D, and protein; lactose content aligns with standard dairy milk. Refrigerate at 34â€“40Â°F (1â€“4Â°C). Unopened cartons last until the sell-by date; shake before use if settled. Variants may include ultra-pasteurized and fortified options.', 290, '233-2335877_whole-milk-gallon-whole-milk-in-the-philippines-removebg-preview.png', 9),
(41, 'Salmon (1.5kg)', 'fish', 'Salmon is a premium, nutrient packed fish thatâ€™s not only delicious but also incredibly versatile. Known for its rich, buttery flavor and tender, flaky texture, it&#39;s a favorite for grilling, baking, or even enjoying raw in sushi. Packed with high quality protein, heart healthy omega-3 fatty acids, and essential vitamins, it&#39;s a perfect choice for anyone looking to elevate their meals while staying healthy. Whether youâ€™re cooking for a special occasion or preparing a nutritious everyd', 1250, 'pngtree-fresh-salmon-fish-png-image_6697483-removebg-preview.png', 10),
(42, 'Parmesan Cheese (8 oz Shaker)', 'dairies', 'Kraft Parmesan Cheese is a grated/shredded Parmesan style cheese sold in cans, tubs, or resealable pouches. It delivers a salty, sharp, nutty flavor with a granular to fine texture, made from real Parmesan or a Parmesan style blend with potential anti-caking agents. A 1â€“2 tablespoon serving provides about 20â€“25 calories, 1â€“2 g protein, 1â€“2 g fat, and calcium. Great for grating over pasta, risotto, soups, and salads, and it melts into sauces. ', 158, 'large_0b127b40-a5a4-406f-b66d-63f8c04356ab-removebg-preview.png', 8),
(43, 'Danablu Blue Cheese', 'dairies', 'Danablu Blue Cheese is a Danish semi-soft blue cheese made from cowâ€™s milk, known for creamy, spreadable texture and distinctive blue veining. It offers a mild to masty, tangy flavor with a clean, creamy finish; rind is typically edible. Common forms include wedges or blocks and crumbles for salads. Nutritionally, blue cheeses provide calcium, protein, and fat, with flavor intensity increasing with age. ', 241, 'danablu-cheese-113573-removebg-preview.png', 9),
(44, 'Lemon (250g)', 'fruits', 'A bright yellow citrus fruit known for its tangy and refreshing flavor. Lemons are rich in vitamin C and add a zesty touch to both sweet and savory dishes. Perfect for making lemonade, enhancing flavors in cooking, or adding a burst of freshness to drinks.\r\n', 75, 'craiyon_122747_image.png', 10),
(45, 'Nestle Greek Yogurt', 'dairies', 'Nestle Greek Yogurt is a thick, high-protein cultured dairy made from skim or whole milk and strained for a creamy, velvety texture. It features a tangy, rich flavor with a creamier mouthfeel than regular yogurt, and comes in plain and flavored varieties in single serve cups or multi pack tubs. Nutritionally, itâ€™s a good source of calcium and protein with relatively lower sugar than many fruit on the bottom yogurts.', 65, 'GreekStyleYogurt_CI2_1200x1200-removebg-preview.png', 9),
(47, 'Magnum Almond Bar', 'dairies', 'Magnum Almond Bar is a premium ice cream bar featuring a creamy vanilla ice cream core coated in a rich, chocolatey shell studded with almond pieces. The bar delivers a satisfying crunch from the almonds and a smooth, indulgent chocolate coating, making it a popular treat for shareable desserts or personal indulgence. Packaging typically highlights the almond inclusions and the Magnum brandâ€™s signature glossy chocolate finish.\r\n\r\n', 55, '60354209-removebg-preview.png', 10),
(48, 'Cloud 9 (28g)', 'chocolate', 'A rich and creamy chocolate bar filled with soft caramel, nougat, and crunchy peanuts. Cloud 9 offers the perfect blend of sweetness and texture for an indulgent treat. Enjoy it as a quick snack or a satisfying dessert anytime you crave something sweet.', 9, 'cloud9.png', 9),
(49, 'Magnum Strawberry Panna', 'dairies', 'Magnum Strawberry Panna is a premium ice cream bar featuring a creamy vanilla ice cream center enveloped in a luscious chocolate coating with strawberry swirl and real strawberry pieces. The result is a balanced, fruit forward sweetness with a smooth texture and delightful bite from the crisp outer shell. Packaging highlights the strawberry swirl and Magnumâ€™s iconic glossy finish.', 65, 'MAGNUM_STRAWBERRY_PANNA_1250x-removebg-preview.png', 10),
(51, 'Nestle All Purpose Cream', 'dairies', 'NestlÃ© All Purpose Cream is a versatile dairy cream ideal for cooking and baking. It is a rich, medium-to-thick cream that can be whisked, whipped, or poured to enrich sauces, soups, desserts, and savory dishes. It provides a smooth, creamy texture and a mild dairy flavor that blends easily with sweet or savory ingredients. Packaging typically includes standard cartons or cups, with storage instructions and a best-by date.', 70, '1f0fae8d39b8cdf8fc0044580762b809_1000x-removebg-preview.png', 10),
(52, 'Meiji Mecadamia (64g)', 'chocolate', 'A luxurious Japanese chocolate treat featuring whole roasted macadamia nuts coated in smooth, creamy milk chocolate. Meiji Macadamia offers a perfect balance of crunch and sweetness, making it an elegant snack for chocolate lovers. Ideal for gifting or enjoying anytime.', 154, 'meiji mecadamia.png', 10),
(53, 'Alaska Condensada (370 g)', 'dairies', 'A rich and creamy sweetened condensed milk made from high quality milk and sugar. It has a smooth texture and a naturally sweet flavor, making it a perfect ingredient for both traditional Filipino desserts and modern recipes. Ideal for making leche flan, fruit salads, graham cakes, or as a sweetener for beverages, Alaska Condensada adds a delicious milky richness to any dish.', 57, 'e2f8f56696c3632ed2ac556ebfaae373_large-removebg-preview.png', 10),
(54, 'Bangus/Milk Fish (1Kg)', 'fish', 'Bangus, often referred to as the â€œnational fishâ€ of the Philippines, is beloved for its soft, tender flesh and delicate flavor. This fish is incredibly versatile, lending itself well to a variety of cooking methods, from grilling and frying to stewing in dishes. Rich in protein, omega-3 fatty acids, and essential vitamins, Bangus is not only a delicious option but also a nutritious one for families looking to enjoy a heart-healthy meal. Whether enjoyed as a whole fish or in fillets, its mild', 190, 'klipartz.com-removebg-preview.png', 9),
(55, 'Alaska Evaporated Milk (360 ml)', 'dairies', 'Alaska Evaporated Filled Milk is a creamy and versatile milk product made from high quality milk, expertly processed to remove about 60% of its water content. This gives it a richer and more flavorful taste than regular fresh milk. Perfect for both sweet and savory dishes, it enhances the creaminess of Filipino classics like sopas, leche flan, maja blanca, and creamy sauces.', 36, 'New-Project-10-removebg-preview.png', 10),
(56, 'Yellowfin Tuna (1Kg)', 'fish', 'Tuna is known for its meaty texture and rich flavor, making it one of the most sought after fish in both casual and fine dining. Yellowfin tuna, in particular, is popular for its versatility. It can be enjoyed raw in sushi or sashimi, grilled, or even seared for a delicious, healthful meal. This fish is an excellent source of lean protein and omega-3 fatty acids, which are beneficial for heart and brain health. Whether served as a steak or in a vibrant kinilaw, Tuna is the perfect choice for sea', 650, 'pngtree-the-yellowfin-tuna-on-transparent-background-png-image_13257661-removebg-preview.png', 10),
(57, 'Nips (40g)', 'chocolate', 'Colorful candy-coated chocolate bites that are fun, sweet, and delicious. Nips combine a crisp candy shell with smooth milk chocolate inside, creating a playful treat loved by all ages. Perfect for snacking, sharing, or adding a burst of color to desserts.', 20, 'nips.png', 10),
(58, 'Toblerone (100g)', 'chocolate', 'A world-famous Swiss chocolate bar known for its unique triangular shape and honey-almond nougat filling. Toblerone combines smooth milk chocolate with crunchy bits for a rich and satisfying taste. Perfect for gifting, sharing, or enjoying as a luxurious treat.', 150, 'toblerone.png', 10),
(59, 'Maya-maya/Red Snapper (1Kg)', 'fish', 'Maya-maya is renowned for its mild, sweet flavor and tender, flaky meat, making it a favorite for both casual family meals and festive feasts. Its versatility allows it to be cooked in various ways, from grilling and frying to steaming or using it in a sour broth like sinigang. This fish is not only delicious but also offers nutritional benefits, including high levels of omega-3 fatty acids, which support heart health. Maya-mayaâ€™s fresh, delicate taste and nutritional value make it an excellen', 450, 'pngtree-fresh-red-snapper-fish-png-image_3782742-removebg-preview.png', 10),
(60, 'Pampano/Pomfret (1Kg)', 'fish', 'Pampano, or pomfret, is a highly regarded fish for its delicate, slightly sweet flavor and smooth, tender texture. Popular in Filipino seafood dishes, itâ€™s typically grilled or steamed and is often served with a simple dipping sauce to highlight its natural taste. Rich in protein and low in fat, Pampano is a nutritious and flavorful choice for those looking to enjoy a healthy meal. Its versatility and mild flavor make it ideal for a variety of cooking styles, and itâ€™s a favorite for both cas', 150, 'pngtree-gold-pomfret-png-image_2443837-removebg-preview.png', 10),
(61, 'Hershey&#39;s (43g)', 'chocolate', 'An iconic chocolate brand loved for its smooth, creamy texture and rich cocoa flavor. Hersheyâ€™s offers a classic taste thatâ€™s perfect for snacking, baking, or melting into sâ€™mores. A timeless treat that brings sweetness to every moment.', 55, 'hersheys.png', 10),
(62, 'Hito/Catfish (1Kg)', 'fish', 'Hito, or catfish, is an affordable and widely enjoyed fish in the Philippines, known for its firm texture and earthy flavor. Often enjoyed fried, grilled, or used in sinigang, Hito is a delicious and nutritious option thatâ€™s easy to cook. Itâ€™s rich in protein and low in fat, making it a healthy choice for everyday meals. The fishâ€™s affordability and versatility in different dishes have made it a beloved staple in Filipino households.', 150, 'channel-catfish-stock-photography-royalty-free-hd-sea-fish-2c9c2bb8b1dc4b164b07d9e291232927.png', 10),
(63, 'Reese&#39;s', 'chocolate', 'A delicious combination of smooth milk chocolate and creamy peanut butter in one iconic treat. Reeseâ€™s delivers the perfect balance of sweet and salty flavors that chocolate lovers crave. Ideal for snacking, sharing, or adding to your favorite desserts.', 62, 'reeses.png', 10),
(64, 'Sea Bass (1Kg)', 'fish', 'Bass, whether is a prized fish known for its firm, white meat and mild, slightly sweet flavor. Itâ€™s often regarded as a delicacy in many culinary circles for its versatility and high culinary value. Whether youâ€™re grilling, steaming, or baking, Bass can easily absorb the flavors of marinades and spices, making it ideal for a variety of recipes. Its mild flavor and tender, flaky texture make it a perfect option for those who prefer a less &#34;fishy&#34; taste, appealing to both seafood enthu', 650, 'pngtree-raw-sea-bass-fresh-seabass-fish-on-transparent-background-p-removebg-preview.png', 10),
(65, 'Kitkat (45kg)', 'chocolate', 'A crisp wafer covered in smooth milk chocolate, offering the perfect balance of crunch and sweetness. KitKat is known for its signature â€œsnapâ€ and deliciously light texture. Enjoy it as a quick snack, a treat to share, or a break-time favorite.', 45, 'craiyon_132502_image.png', 10),
(66, 'Mackerel (1Kg)', 'fish', 'Mackerel is a flavorful and highly nutritious fish thatâ€™s popular in many Filipino households, known for its rich, oily texture and slightly bold taste. Often referred to as &#34;Bangus&#34; when prepared locally, itâ€™s an ideal fish for grilling, frying, or making in various Filipino dishes like sinigang and paksiw. Mackerel&#39;s natural oils give it a moist, tender consistency, making it perfect for those who love fish with a bit more flavor depth.\r\n\r\nPacked with high-quality protein and o', 250, 'pngtree-streamed-mackerel-fish-png-image_11933641-removebg-preview.png', 10),
(67, 'Kisses (200g)', 'chocolate', 'Iconic bite-sized chocolates wrapped in shiny foil, known for their smooth, creamy flavor and signature shape. Hersheyâ€™s Kisses are perfect for sharing, gifting, or enjoying one sweet moment at a time. A timeless treat that spreads love with every piece.', 210, 'kisses.png', 10),
(68, 'Coca Cola 1L', 'drinks', 'Coca Cola is a world famous carbonated soft drink known for its classic, refreshing taste. Made with a unique blend of natural flavors, carbonation, and just the right amount of sweetness, Coca Cola delivers a crisp and uplifting drinking experience. Perfect for sharing with family and friends, enjoying during meals, or cooling off on a hot day, Coca Cola has been a trusted favorite for over a century.', 75, 'coca-cola-xxxi-olcc-thumb-5-removebg-preview.png', 10),
(69, 'Pepsi (1.5 L)', 'drinks', 'Pepsi is a bold and refreshing carbonated soft drink with a distinctly smooth and sweet cola flavor. Known for its vibrant taste and crisp fizz, Pepsi is perfect for quenching your thirst, pairing with meals, or enjoying on any occasion. Its lively and youthful spirit makes it a popular choice worldwide.', 85, 'SM9198577-3-removebg-preview (1).png', 10),
(70, 'Dr Pepper (12 oz)', 'drinks', 'Dr Pepper is a unique and refreshing carbonated soft drink with a distinct blend of 23 signature flavors. Known for its bold, smooth, and slightly spicy taste, Dr Pepper offers a one of a kind drinking experience that sets it apart from regular colas. Perfect for enjoying on its own, pairing with snacks, or serving at gatherings, Dr Pepper has been a favorite for those who crave something different.', 77, 'DrPepperCans12FluidOunce_1_1200x-removebg-preview.png', 10),
(71, 'Rainbow Trout (1Kg)', 'fish', 'Rainbow Trout is a prized freshwater fish known for its delicate, mild flavor and tender, flaky texture. Itâ€™s widely appreciated for its subtle sweetness and rich, buttery taste, making it a favorite among gourmet food lovers. Whether pan-seared, grilled, or baked, trout cooks quickly and is versatile enough to pair with a variety of herbs, spices, or citrus. Its light, flaky flesh makes it a perfect match for both simple, quick preparations and more elaborate gourmet recipes.\r\n\r\nBeyond its gr', 850, 'pngtree-realistic-rainbow-trout-png-image_17314376-removebg-preview.png', 10),
(72, 'ChocoMucho (33g)', 'chocolate', 'A crunchy wafer bar layered with caramel, rice crispies, and rich chocolate coating. ChocoMucho delivers a perfect mix of texture and sweetness in every bite. Ideal for satisfying your chocolate cravings anytime, anywhere.', 10, 'chocomucho.png', 10),
(73, 'Cobra (Green)', 'drinks', 'Cobra Green is a refreshing energy drink crafted to give you a powerful boost of energy and focus. Known for its crisp, citrus flavor and smooth finish, Cobra Green helps keep you energized and alert whenever you need it most. Ideal for busy days, long nights, or active lifestyles, this drink delivers fuel for both mind and body.', 23, 'images-removebg-preview.png', 10),
(74, 'Cobra (Red)', 'drinks', 'Cobra Red is a high energy drink specially formulated to help boost alertness, endurance, and performance. With its bold, sweet flavor and invigorating kick, Cobra Red provides the extra energy you need to stay focused and energized throughout the day. Perfect for students, workers, and anyone needing a quick pick me up, Cobra Red delivers maximum power in every sip.', 23, 'SM10666354-6-removebg-preview.png', 10),
(75, 'Jinro Soju Strabbery', 'drinks', 'Jinro Soju Strawberry is a smooth and refreshing Korean spirit infused with the sweet and fruity flavor of ripe strawberries. This popular variant of Jinro Soju offers a delightfully light taste with a perfect balance of sweetness and a mild kick, making it enjoyable for both new drinkers and soju enthusiasts. Best served chilled, itâ€™s perfect for casual gatherings, parties, or pairing with your favorite dishes.', 139, 'JinroSojuStrawberry360ml-removebg-preview.png', 9),
(76, 'Jinro Chamisul Original Soju', 'drinks', 'Jinro Chamisul Original Soju is a classic Korean distilled spirit known for its clean, crisp taste and smooth finish. Made from high quality ingredients and filtered through charcoal bamboo, this original variant delivers a refreshing yet bold flavor with a higher alcohol content compared to flavored sojus. Perfect for traditional Korean drinking sessions, parties, or pairing with grilled dishes, Jinro Chamisul Original remains a top choice for authentic soju lovers', 139, 'Jinro_Fresh_99b0a6f4-a22c-43cc-9ecc-d37f5fb2b46e_1024x-removebg-preview.png', 10),
(77, 'Pocky (40g)', 'chocolate', 'A popular Japanese snack made of crunchy biscuit sticks coated in smooth, creamy chocolate. Pocky offers a fun and shareable treat with a perfect balance of sweetness and crunch. Available in a variety of flavors, itâ€™s ideal for snacking, sharing, or gifting.', 50, 'pocky.png', 12),
(78, 'Heineken Beer (330 ml)', 'drinks', 'Heineken Beer is a premium quality lager brewed with 100% natural ingredients water, malted barley, and hops and Heinekenâ€™s unique A yeast, which gives it its distinctly crisp, smooth, and mildly bitter taste. Known for its iconic green bottle and red star, Heineken delivers a refreshing, well balanced flavor thatâ€™s perfect for any occasion  from casual get togethers to celebrations.', 89, 'Heineken_330mL_Bottle_Pure_Malt_Lager_New_1429265a-9d86-4ecc-ba84-7d10efc1ee33-removebg-preview.png', 10),
(79, 'C2 (Apple Green)', 'drinks', 'C2 Apple Green Tea is a refreshing ready to drink beverage made from 100% natural green tea leaves, infused with the crisp and fruity flavor of fresh apples. Itâ€™s brewed and bottled on the same day to lock in the natural tea goodness and provide a smooth, lightly sweet taste thatâ€™s perfect for any time of the day. Ideal for cooling down, pairing with meals, or enjoying on the go.', 23, 'c2_apple-removebg-preview.png', 10),
(80, 'Croissant ', 'bread', 'A croissant is a buttery, flaky, and indulgent pastry that originated in France, now loved around the world for its delicate texture and rich flavor. Made from layers of dough and butter, then baked to golden perfection, the croissant is a delightful treat that offers a satisfying combination of crispy edges and soft, airy interior. It&#39;s the perfect choice for breakfast, a snack, or even paired with a cup of coffee for a quick but luxurious pick-me-up.\r\n\r\nWhat makes croissants so special is ', 65, 'croissant-french-cuisine-bakery-breakfast-za-atar-croissant-9312bf93be6053b2f46314da39996b1f.png', 10),
(81, 'C2 (Green Tea)', 'drinks', 'C2 Green Tea Original is a refreshing ready to drink beverage made from 100% natural green tea leaves, brewed and bottled on the same day to seal in the teaâ€™s natural goodness. With its lightly sweet, smooth, and authentic green tea flavor, itâ€™s the perfect drink for cooling down, recharging, and staying refreshed throughout the day.', 23, 'images__2_-removebg-preview.png', 10),
(82, 'Pandesal', 'bread', 'Pandesal is a beloved Filipino bread roll thatâ€™s soft, slightly sweet, and perfect for any time of day. Known for its golden, slightly crunchy exterior and warm, pillowy interior, pandesal is a staple in Filipino households and is often enjoyed fresh out of the oven with a cup of coffee or hot chocolate. Its slightly sweet flavor makes it the ideal companion for both savory fillings like cheese, eggs, or deli meats, and sweet spreads like jam, peanut butter, or butter.\r\n\r\nThe appeal of pandesa', 5, 'bun-pandesal-coco-bread-small-bread-bun-7325aaafe241b76a1677357ac6d0db75.png', 10),
(83, 'Ensaymada', 'bread', 'Ensaymada is a sweet, buttery, and soft bread roll often topped with grated cheese and sugar. Itâ€™s a favorite treat for special occasions like birthdays and holidays but is also commonly enjoyed as a snack or dessert. The rich and fluffy texture, combined with its sweet and savory topping, makes ensaymada a luxurious and indulgent bread that is loved by all ages.', 40, 'ensaimada-filipino-cuisine-recipe-cake-dish-cake-5193fd3ccddcd6dd7d17a6b1331275eb.png', 10),
(84, 'Cabbage (1kg)', 'vegetables', 'A leafy green vegetable known for its crisp texture and mild flavor. Cabbage is rich in vitamins C and K, and itâ€™s great for promoting a healthy diet. Perfect for salads, stir-fries, soups, or as a crunchy side dish.', 94, 'cabbage.png', 9),
(85, 'Pan De Coco', 'bread', 'Pan de coco is a Filipino bread roll filled with sweetened coconut. The soft, slightly sweet bread encases a filling of grated coconut and sugar, offering a delightful balance of flavors. This bread is often enjoyed as a snack, especially with a cup of coffee or tea. The contrast of the fluffy bread with the chewy coconut filling gives it a unique and comforting taste thatâ€™s hard to resist.', 8, 'pan-de-coco-650x650.png', 9),
(86, 'Turkey (1 kg)', 'meat', 'Meat is a rich source of protein, essential nutrients, and natural flavor, making it a staple ingredient in cuisines around the world. Sourced from high quality livestock and poultry, meat can be prepared in a variety of ways grilled, roasted, fried, or stewed to suit any dish. Whether red meat, white meat, or seafood, it offers tender texture, savory taste, and nutritional value that make it perfect for everyday meals and special occasions.', 550, 'istockphoto-507755834-612x612-removebg-preview.png', 10),
(87, 'Cinnamon Roll', 'bread', 'Cinnamon rolls are a beloved pastry in the Philippines, often enjoyed as a sweet breakfast or dessert. The soft, buttery dough is swirled with cinnamon and sugar, then topped with icing or cream cheese. These rolls are loved for their comforting warmth and the perfect balance of sweetness and spice. Whether fresh out of the oven or from your favorite bakery, cinnamon rolls never fail to satisfy a sweet tooth.', 65, 'ppqkbtmffqh2l2a2vume7pjc00.png', 10),
(88, 'Cucumber (1kg)', 'vegetables', 'A cool and refreshing vegetable with a crisp texture and mild flavor. Cucumbers are high in water content and packed with vitamins, making them perfect for hydration. Ideal for salads, sandwiches, or as a healthy, crunchy snack.', 82, 'pngtree-whole-cucumber-with-slices-and-a-half-png-image_2675128-removebg-preview.png', 10),
(89, 'Pork Chop (1 kg)', 'meat', 'Pork Chop is a cut of meat taken from the loin of a pig, typically including a portion of the rib bone (or sometimes boneless). It offers a balance of tenderness, flavor, and fat that makes it ideal for grilling, pan frying, baking, or broiling. With its juicy texture and savory profile, pork chop pairs well with herbs, marinades, sauces, and sides like vegetables, mashed potatoes, or rice.', 285, 'pork_rib_chop-589e6f303df78c4758388334.jpg', 9),
(90, 'Baguette', 'bread', 'A Baguette is the epitome of classic French bakingâ€”long, golden, and crispy on the outside with a soft, airy interior. Known for its distinctive shape and crunchy crust, this French bread is a symbol of culinary elegance. The Baguette is perfect for pairing with cheeses, cold cuts, or spreads, and itâ€™s often served alongside meals like soup or salad. Itâ€™s also wonderful for creating gourmet sandwiches, with its texture providing the ideal balance between crispness and chewiness.\r\n\r\nWhat ma', 150, 'baguette-bread-beer-baguette-bread-9c9ccd4e5800eeb41291fb809b70cd38.png', 10),
(91, 'TJ Hotdog (1 kg)', 'meat', 'TJ Hotdog (Tender Juicy Hotdog) is a beloved Filipino favorite a fully cooked sausage made from a blend of premium pork (and/or beef), seasoned for a balance of savory and slightly sweet flavor. Itâ€™s known for its juicy texture, vibrant reddish hue, and ability to absorb marinades and grill flavors well.', 180, '105938-1.jpg', 10),
(92, 'Carrot (1kg)', 'vegetables', 'A crunchy, sweet root vegetable known for its bright orange color and rich nutrients. Carrots are packed with vitamin A and antioxidants that support good vision and overall health. Perfect for salads, soups, stir-fries, or as a healthy snack.', 242, 'carrot.png', 10),
(93, 'Beef Sirloin (1kg)', 'meat', 'Beef sirloin is a tender cut from the back of the cow, between the short loin and the round. Itâ€™s known for having a good balance of flavor, tenderness, and relatively moderate fat, making it a versatile cut that works well for steaks, roasting, grilling, or slicing thin for dishes like bistek Tagalog or stir-fries.', 512, 'Top_Sirloin_Steak-removebg-preview.png', 10),
(94, 'Sour Dough Bread (Loaf)', 'bread', 'Sourdough isnâ€™t just breaditâ€™s a living, breathing piece of history. Known for its slightly tangy flavor and chewy texture, this classic bread has been around for centuries, and for good reason. Made with wild yeast and bacteria, the fermentation process imparts a unique, rich flavor that regular breads canâ€™t match. Whether youâ€™re spreading creamy butter on a fresh slice or using it for your next sandwich, sourdoughâ€™s complex taste will elevate even the simplest meal. Plus, it&#39;s ea', 250, 'rye-bread-baguette-sourdough-bakery-poppy-seed-4c8ffdebb3bb84a3e5688314a2da3724.png', 10),
(95, 'Beef Tenderloin (1kg)', 'meat', 'Beef Tenderloin is one of the most premium cuts of beef. It comes from the loin, specifically the psoas major muscle, which is minimally used; this means it has very little connective tissue and fat, making it extremely tender. Because it&#39;s so soft and mild, its flavor is delicate, not as strongly beefy as more marbled cuts, but it shines through when cooked properly.', 2339, '300-330g-steak-tenderloin-steak-choice-28957154377908_600x600-removebg-preview.png', 10),
(96, 'Eggplant (1kg)', 'vegetables', 'A versatile vegetable with smooth purple skin and tender flesh. Eggplants are rich in fiber and antioxidants, making them a healthy addition to any meal. Perfect for grilling, roasting, stir-frying, or adding to savory dishes and stews.', 122, 'eggplant.png', 10),
(97, 'Ciabatta (Loaf)', 'bread', 'Ciabatta is Italy&#39;s answer to a rustic, flavorful bread thatâ€™s perfect for any meal. Its airy, open crumb and crispy crust are a result of a special dough thatâ€™s wetter than usual, giving it an incredibly light texture. Whether served with a drizzle of olive oil, used for a sandwich, or enjoyed as a side to pasta, Ciabatta brings a certain authenticity to every dish. Its simple yet robust flavor makes it a favorite among bread enthusiasts. Itâ€™s the perfect bread for those who appreciat', 220, 'ciabatta-toast-pandesal-rye-bread-bakery-toast-949a1bb86ad11acee1e0b392aaf0b2af.png', 10),
(98, 'Focaccia (Loaf)', 'bread', 'Focaccia is an Italian flatbread that offers a delightful combination of a crispy, golden crust and a soft, olive-oil-infused interior. Topped with rosemary, garlic, or even olives, Focaccia is a savory delight that can be served as a side to pasta, a sandwich base, or even enjoyed on its own. The flavor is simple yet rich, and the texture is perfect for tearing and sharing. With every bite, Focaccia delivers a satisfying crunch and a burst of flavor that transforms any meal into a true Italian ', 200, 'pide-bakery-pita-focaccia-bread-bread-561f6a6f2ff9b204980fa7136572da2b.png', 10),
(100, 'Ground Pork (1kg)', 'meat', 'Ground pork (also called â€œginiling na baboyâ€ in Filipino) is pork meat that has been finely minced or processed, combining lean cuts and fat to give it flavor, moisture, and versatility. Itâ€™s a staple ingredient in many cuisines and dishes because it absorbs seasonings well and cooks relatively fast.', 297, 'products-Ground-Pork-Regular-2-removebg-preview.png', 8),
(101, 'Pita', 'bread', 'Pita is more than just flatbread, it&#39;s the ideal companion for any meal. With its soft, chewy texture and the magical pocket that forms during baking, itâ€™s as versatile as it is delicious. Enjoy it fresh from the oven and stuff it with your favorite fillingsâ€”be it falafel, grilled chicken, or your favorite veggies. Pita is equally wonderful as a dipper for hummus or baba ghanoush. Itâ€™s a no-fuss, healthy bread that adds flavor and texture without overwhelming the rest of your meal. Wh', 30, 'pita-arab-cuisine-bakery-syrian-cuisine-baguette-bread-ba5f90d65759647dc35d65478312c15c.png', 10),
(102, 'Skinless Chicken Logganisa (1pc)', 'meat', 'Skinless Chicken Longganisa is a healthier twist on traditional Filipino longganisa. Made from ground chicken instead of pork, this version omits casings (â€œskinlessâ€) and is typically shaped using parchment or wax paper. It features aromas and flavors from garlic, sugar (often muscovado or brown), vinegar or soy sauce, pepper, and regional spice blends. Itâ€™s sweeter than salty or garlicky longganisa types, though versions vary. Because it uses leaner meat, itâ€™s lighter and more tender wh', 10, 'SkinlessChickenLongganisa_40e5b600-30b2-46a7-8a86-c2b78f843978_1200x1200-removebg-preview.png', 10),
(103, 'Tortilla (10 Pcs)', 'bread', 'When it comes to Tortilla, simplicity meets versatility. This thin, flatbread is at the heart of many Latin American dishes, from tacos to burritos, and everything in between. Made from corn or flour, Tortillas are the perfect foundation for bold flavors, whether youâ€™re wrapping them around grilled meats, fresh veggies, or cheese. Its flexible nature and mild flavor make it ideal for any meal. Plus, Tortillas are easy to make at home, and once you taste the fresh, warm version, youâ€™ll never ', 130, '7l9n4kvufk0i0mbr6vcq7uo4jv.png', 8),
(104, 'CDO Tocino (450 g)', 'meat', 'CDO Tocino (branded CDO Funtastyk Tocino) is a popular Filipino marinated sweet cured meat made from 100% young pork (or boneless chicken, depending on variant). Itâ€™s pre seasoned with a signature blend of sweet and salty flavors, and designed to be easy to cook just pan-fry (no need to boil first). Variants include Young Pork Tocino (regular), Fatless, Chili, and Chicken Tocino. Great for breakfast (tocilog), meals, or adding flavor to many dishes.', 140, 'cdotocino-Copy-removebg-preview.png', 8),
(106, 'Ground Beef (1kg)', 'meat', 'Ground beef is beef that has been finely chopped or minced, combining lean meat and fat. Itâ€™s one of the most versatile types of beef you can use. Because of its texture and composition, it cooks relatively quickly and absorbs flavors well, making it ideal for many dishes.', 340, '82_037930de-3ada-4ae7-aa7e-bff01637a9eb__1_-removebg-preview.png', 8),
(107, 'Tomato (1Kg)', 'vegetables', 'Tomatoes are more than just a salad staple, theyâ€™re the juicy, tangy foundation of many Filipino dishes. Whether theyâ€™re blended into sauces, tossed into a refreshing salad, or served as a side to your adobo or sinigang, tomatoes add a burst of flavor and color to every meal. Their natural sweetness and acidity make them perfect for balancing savory, salty, and spicy flavors. Available year-round, theyâ€™re affordable, versatile, and easy to incorporate into nearly any dish. Tomatoes are a k', 65, 'tomato-clip-art-tomato-png-clipart-c96fd0e259c5116b6afd209901b82085.png', 6),
(108, 'Red Onion (!Kg)', 'vegetables', 'Onions are a foundational vegetable in Filipino cooking, offering depth of flavor in almost every savory dish. Whether sautÃ©ed, caramelized, or used as a base for soups and stews, onions bring a savory sweetness that complements meats, vegetables, and sauces. Known for their distinct flavor, onions are essential in dishes like sinigang, adobo, or nilaga, and their versatility in both cooked and raw forms makes them indispensable. Not only do they elevate the taste of your food, but onions are a', 110, 'red-onion-food-vegetable-shallot-yellow-onion-onion-5a558e4d17f5be4d99b01c9fbc50a80e.png', 5),
(109, 'Kale (Bunch)', 'vegetables', 'Kale is a leafy green vegetable that has gained popularity for its remarkable nutritional profile. Often used in salads, smoothies, or as a healthy addition to soups and stews, kale is rich in vitamins A, C, and K, as well as antioxidants. Its hearty, slightly bitter taste is perfect for those looking for something a little more robust in their leafy greens. Kaleâ€™s versatility makes it ideal for making everything from healthy wraps to sautÃ©ed vegetable dishes. If youâ€™re looking for a nutrie', 200, 'kale-romaine-lettuce-food-vegetable-calcium-kale-6ae3db73cdde5770317fc452c24d2275.png', 8),
(110, 'Pechay (1kg)', 'vegetables', 'A leafy green vegetable commonly used in Filipino dishes, known for its mild flavor and tender texture. Pechay is rich in vitamins A and C, calcium, and fiber, making it both healthy and delicious. Perfect for soups, stir-fries, and steamed dishes.', 148, 'pechay.png', 3),
(111, 'Ginger (1kg)', 'vegetables', 'A fragrant and spicy root used to add warmth and flavor to many dishes. Ginger is known for its health benefits, including aiding digestion and boosting immunity. Perfect for cooking, making tea, or adding a zesty kick to soups and marinades.', 214, 'ginger.png', 4),
(112, 'Ampalaya/Bittermelon (1Kg)', 'vegetables', 'Ampalaya, also known as bittermelon, is a unique and highly nutritious vegetable cherished in Filipino cuisine. Despite its slightly bitter flavor, ampalaya is loved for the depth it adds to savory dishes, and its health benefits make it a popular choice among health-conscious eaters. Often cooked in dishes like Ampalaya con Carne (stir-fried with beef) or Sinigang na Ampalaya (a twist on the classic sour soup), its bitterness perfectly balances rich and savory flavors.\r\n\r\nBittermelon is more th', 90, 'pngtree-bitter-gourd-png-image_2451202-removebg-preview.png', 8);

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `rating` int(1) NOT NULL CHECK (`rating` between 1 and 5),
  `review_text` varchar(500) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `user_id`, `product_id`, `rating`, `review_text`, `image`, `created_at`) VALUES
(1, 34, 112, 5, 'It tastes good.', NULL, '2025-11-03 05:48:40'),
(2, 34, 112, 5, 'It tastes good.', NULL, '2025-11-03 05:49:16'),
(3, 34, 112, 5, 'It tastes good.', NULL, '2025-11-03 05:49:40'),
(4, 34, 112, 5, 'It tastes good.', NULL, '2025-11-03 05:51:28'),
(5, 34, 112, 5, 'It tastes good.', NULL, '2025-11-03 05:51:34'),
(6, 34, 112, 5, 'It tastes good.', NULL, '2025-11-03 05:51:44'),
(7, 34, 112, 5, 'It tastes good.', NULL, '2025-11-03 05:52:02'),
(8, 34, 112, 5, 'WOWWW', NULL, '2025-11-03 06:10:22'),
(9, 34, 112, 4, 'YUMMY', '1762150684_Ampalaya_2048x2048.webp', '2025-11-03 06:18:04'),
(10, 50, 111, 5, 'WOWOWOW', NULL, '2025-11-03 06:58:37');

-- --------------------------------------------------------

--
-- Table structure for table `stock_history`
--

CREATE TABLE `stock_history` (
  `id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `quantity_added` int(11) NOT NULL,
  `new_total_stock` int(11) NOT NULL,
  `restock_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stock_history`
--

INSERT INTO `stock_history` (`id`, `product_id`, `admin_id`, `quantity_added`, `new_total_stock`, `restock_date`) VALUES
(1, 26, 35, 12, 19, '2025-10-18 07:10:47'),
(2, 26, 35, 121, 140, '2025-10-18 07:12:34'),
(3, 27, 35, 123, 132, '2025-10-18 07:12:47'),
(4, 27, 35, 20, 30, '2025-10-27 18:27:50'),
(5, 27, 35, -6, 24, '2025-11-06 18:16:07'),
(6, 27, 35, 11, 35, '2025-11-06 18:16:10');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `number` varchar(15) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `user_type` enum('admin','user') NOT NULL DEFAULT 'user',
  `image` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `status` enum('active','inactive','banned') DEFAULT 'active',
  `address` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `number`, `email`, `password`, `user_type`, `image`, `created_at`, `status`, `address`) VALUES
(33, 'Admin B', NULL, 'adminb@gmail.com', '21232f297a57a5a743894a0e4a801fc3', 'admin', 'dog.jpg', '2025-10-16 23:14:17', 'active', NULL),
(34, 'Christian Lacerna', NULL, 'cjlacerna@gmail.com', '$2y$10$3Xr11OpgqpV8z0myfwkvduFZojBvvlYkYha3pGKwMkY6xtrkt7boK', 'user', 'dog.jpg', '2025-10-16 23:14:17', 'active', NULL),
(35, 'Admin', NULL, 'admin@gmail.com', '$2y$10$IIFO9nboLDYsz1gSbSqDE.ZDx1yu3hofHYvjDQfz1BZAXtj1pZD72', 'admin', 'admin.png', '2025-10-16 23:14:17', 'active', NULL),
(37, 'qshop', NULL, 'qshop@gmail.com', '202cb962ac59075b964b07152d234b70', 'user', 'chicken.png', '2025-10-16 23:14:17', 'active', NULL),
(38, 'quickshop', NULL, 'quickshop@gmail.com', '202cb962ac59075b964b07152d234b70', 'user', 'chicken.png', '2025-10-16 23:14:17', 'active', NULL),
(39, 'tian', NULL, 'tian@gmail.com', '202cb962ac59075b964b07152d234b70', 'user', 'pfp.jpg', '2025-10-16 23:14:17', 'active', NULL),
(40, 'karl', NULL, 'karl@gmail.com', '202cb962ac59075b964b07152d234b70', 'user', 'pfp.jpg', '2025-10-16 23:14:17', 'active', NULL),
(41, 'Karl ALalay', NULL, 'karlalalay@gmail.com', '202cb962ac59075b964b07152d234b70', 'user', 'pfp.jpg', '2025-10-16 23:14:17', 'active', NULL),
(42, 'Christian Lacerna', NULL, 'lacerna@gmail.com', '4297f44b13955235245b2497399d7a93', 'user', 'aboutuspic2.png', '2025-10-16 23:14:17', 'active', NULL),
(43, 'Jennard Joseph Palijado', NULL, 'jennard@gmail.com', '23a56d8fcaa1084a39afacb137335dff', 'user', 'aboutuspic2.png', '2025-10-17 15:21:40', 'active', NULL),
(44, 'Christian Lacerna', NULL, 'cjlacerna1@gmail.com', '$2y$10$5RuywqxvyCO6UaA3bGvjb.Y4m4pLVZF/zP0WOnnWRiI2bQw2iJoKK', 'user', 'pfp.jpg', '2025-10-23 19:23:28', 'active', NULL),
(45, 'Christian Lacerna', NULL, 'cjlacerna123@gmail.com', '$2y$10$gxEO89X8McQYDXtbfQ5ASuPh7vodH0r4OfCpBa25GjQoht1T2Wb9q', 'user', 'pfp.jpg', '2025-10-23 19:29:33', 'active', '100, Dela Paz , City of Antipolo , Rizal, Region IV-A (CALABARZON)'),
(46, 'Xtian Lacerna', NULL, 'xtianjoshua@gmail.com', '$2y$10$QiGMGrKvya.0ixz8OK1/COwIBCekrEleQ3.BBbNAVe/69LpJgo6xu', 'user', 'pfp.jpg', '2025-10-23 19:45:48', 'active', 'Region IV-A (CALABARZON), Rizal, City of Antipolo , Dela Paz , 100'),
(47, 'Christian Lacerna', NULL, 'cjlacerna3@gmail.com', '$2y$10$jfUaoMXY4zHNyV6.xjSOcuMI3JAMO.BXpuTeq/Z0/Lkwsr8bRAN1e', 'user', 'pfp.jpg', '2025-10-23 19:52:55', 'active', 'Region IV-A (CALABARZON), Rizal, City of Antipolo , Dela Paz , 100'),
(48, 'Xtian Joshua Lacerna', '09123456789', 'xtianjoshual@gmail.com', '$2y$10$j9OtAy6NTWN773T4cdM/OOTcBiyqdJunkV4dKnoyTP4l5WRsMyaAG', 'user', 'pfp.jpg', '2025-10-23 21:07:15', 'active', 'Region IV-A (CALABARZON), Rizal, City of Antipolo , Dela Paz , 100'),
(49, 'Karl Alalay Pogi', '09123456789', 'karlpogi@gmail.com', '$2y$10$x/L5VVc48J5.X1ggOLngaeUjM8DBVg5h7hI1s2B4Lk8qi/.tgaG5m', 'user', 'watermelon.png', '2025-10-23 22:50:34', 'active', 'National Capital Region (NCR), , City of Marikina, Barangka, 100'),
(50, 'ChristianJ Lacerna', '09123456789', 'cjlacerna6@gmail.com', '$2y$10$AKhWQgbodhYADo4SgiVjm.eaP8g7FoJJRZrbTTZ7AQABAL4CuYJzm', 'user', 'BukoJuice.jpg', '2025-11-03 14:53:11', 'active', 'Region IV-A (CALABARZON), Rizal, City of Antipolo , Dela Paz , 99');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(100) NOT NULL,
  `user_id` int(100) NOT NULL,
  `pid` int(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` int(100) NOT NULL,
  `image` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wishlist`
--

INSERT INTO `wishlist` (`id`, `user_id`, `pid`, `name`, `price`, `image`) VALUES
(52, 37, 24, 'Banana (1kg)', 80, 'banana.png'),
(56, 40, 24, 'Banana (1kg)', 80, 'banana.png'),
(61, 39, 24, 'Banana (1kg)', 80, 'banana.png'),
(62, 39, 26, 'Chicken (1kg)', 300, 'chicken.png'),
(63, 39, 27, 'Fresh Tilapia (1kg)', 220, 'Fresh Tilapia.png'),
(67, 46, 97, 'Ciabatta (Loaf)', 220, 'ciabatta-toast-pandesal-rye-bread-bakery-toast-949a1bb86ad11acee1e0b392aaf0b2af.png'),
(68, 46, 96, 'Eggplant (1kg)', 122, 'eggplant.png'),
(69, 34, 111, 'Ginger (1kg)', 214, 'ginger.png'),
(70, 34, 109, 'Kale (Bunch)', 200, 'kale-romaine-lettuce-food-vegetable-calcium-kale-6ae3db73cdde5770317fc452c24d2275.png');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `message`
--
ALTER TABLE `message`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `philippine_addresses`
--
ALTER TABLE `philippine_addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `stock_history`
--
ALTER TABLE `stock_history`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=136;

--
-- AUTO_INCREMENT for table `message`
--
ALTER TABLE `message`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `philippine_addresses`
--
ALTER TABLE `philippine_addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=113;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `stock_history`
--
ALTER TABLE `stock_history`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `philippine_addresses`
--
ALTER TABLE `philippine_addresses`
  ADD CONSTRAINT `philippine_addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
