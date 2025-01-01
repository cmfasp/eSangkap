-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
<<<<<<< HEAD
-- Generation Time: Jan 01, 2025 at 10:23 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30
=======
-- Generation Time: Jan 01, 2025 at 09:36 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12
>>>>>>> c7309906277cd27614d1627067c583d2e05402ca

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tastebud_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`) VALUES
(5, 'Dessert'),
(6, 'Breakfast'),
(7, 'Lunch'),
(8, 'Dinner'),
(9, 'Vegan Meals'),
(10, 'Salad'),
(11, 'Soup'),
(12, 'Snack');

-- --------------------------------------------------------

--
-- Table structure for table `chat_data`
--

CREATE TABLE `chat_data` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `message_send` text DEFAULT NULL,
  `send_date` date DEFAULT NULL,
  `message_received` text DEFAULT NULL,
  `received_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chat_data`
--

INSERT INTO `chat_data` (`id`, `username`, `message_send`, `send_date`, `message_received`, `received_date`) VALUES
(220, 'sydney', 'what is recipe', '2024-12-13', NULL, '2024-12-13'),
(221, 'sydney', 'what is api', '2024-12-13', NULL, '2024-12-13');

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `comment_id` int(11) NOT NULL,
  `meal_id` int(11) DEFAULT NULL,
  `user_name` varchar(255) DEFAULT NULL,
  `comment_text` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`comment_id`, `meal_id`, `user_name`, `comment_text`, `created_at`) VALUES
(12, 25, 'sydney', 'hello', '2024-12-14 19:24:03'),
(14, 25, 'sydney', 'A web system application for food streamlines food ordering, delivery, and inventory processes. Designed for restaurants, food businesses, or home-based sellers, it enables users to browse menus, customize orders, and make online payments effortlessly. The system integrates with GPS for real-time tracking and offers robust analytics for business insights. It also enhances user experience through personalized recommendations and loyalty rewards, ensuring convenience, efficiency, and customer satisfaction in food services.', '2024-12-14 19:39:53'),
(15, 25, 'sydney', 'A web system application for food revolutionizes the dining and delivery experience. Customers can explore menus, place orders, and pay online securely. Businesses benefit from features like inventory tracking, sales analytics, and customer feedback tools. Real-time delivery tracking ensures transparency, while personalized meal suggestions enhance user satisfaction. This system bridges convenience and technology, helping food businesses grow while providing customers with seamless and enjoyable dining or takeaway experiences.', '2024-12-14 19:50:37');

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `favorite_id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `meal_id` int(11) NOT NULL,
  `date_created` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `favorites`
--

INSERT INTO `favorites` (`favorite_id`, `username`, `meal_id`, `date_created`) VALUES
(24, 'sydney', 25, '2024-12-29 20:04:51'),
(29, 'sydney', 28, '2024-12-29 21:33:59'),
(32, 'sydney', 29, '2024-12-29 21:57:10'),
<<<<<<< HEAD
(33, 'sydney', 30, '2024-12-29 21:57:18'),
(34, 'user', 30, '2024-12-30 16:42:33'),
(35, 'user', 25, '2024-12-30 16:42:42'),
(36, 'user', 29, '2024-12-30 16:42:55'),
(38, 'user', 33, '2024-12-30 18:27:05');
=======
(33, 'sydney', 30, '2024-12-29 21:57:18');
>>>>>>> c7309906277cd27614d1627067c583d2e05402ca

-- --------------------------------------------------------

--
-- Table structure for table `ingredients`
--

CREATE TABLE `ingredients` (
  `ingredient_id` int(11) NOT NULL,
  `meal_id` int(11) NOT NULL,
  `ingredient_name` varchar(255) DEFAULT NULL,
  `alt_ingredients` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ingredients`
--

<<<<<<< HEAD
INSERT INTO `ingredients` (`ingredient_id`, `meal_id`, `ingredient_name`) VALUES
(180, 19, 'fsdfas'),
(181, 19, 'sds'),
(182, 19, 'ds'),
(183, 19, 'fsd'),
(184, 19, 'gfd'),
(185, 19, 'gf'),
(186, 19, ''),
(187, 19, ''),
(188, 19, ''),
(189, 19, ''),
(257, 25, 'Glutinous rice'),
(258, 25, 'Coconut milk'),
(259, 25, 'Brown sugar'),
(260, 25, 'Pandan leaves (optional)'),
(270, 28, '4 cups romaine lettuce (chopped)'),
(271, 28, '1/2 cup Caesar dressing'),
(272, 28, '1/4 cup grated Parmesan cheese'),
(273, 28, '1 cup croutons'),
(274, 28, '1 tbsp olive oil (optional for garnish)'),
(275, 28, '1 tbsp lemon juice (optional)'),
(276, 29, 'Cucumber'),
(277, 29, 'Cherry tomatoes'),
(278, 29, 'Red onion'),
(279, 29, 'Kalamata olives'),
(280, 29, 'Feta cheese'),
(281, 29, 'Olive oil'),
(282, 29, 'Lemon juice'),
(283, 29, 'Oregano'),
(284, 30, 'Fresh mozzarella'),
(285, 30, 'Ripe tomatoes'),
(286, 30, 'Fresh basil'),
(287, 30, 'Olive oil'),
(288, 30, 'Balsamic vinegar'),
(289, 30, 'Salt'),
(290, 30, 'Pepper'),
(291, 31, 'asdfasd,fasdfasd,fasdf'),
(292, 32, 'asdcasd'),
(293, 32, 'acsdcasdc'),
(294, 32, 'asdcasdc'),
(295, 33, 'ing1'),
(296, 33, 'ing2'),
(297, 33, 'ing3'),
(298, 33, 'ing4');
=======
INSERT INTO `ingredients` (`ingredient_id`, `meal_id`, `ingredient_name`, `alt_ingredients`) VALUES
(257, 25, 'Glutinous rice', 'Sticky rice or short-grain sushi rice'),
(258, 25, 'Coconut milk', 'Evaporated milk or almond milk with coconut extract'),
(259, 25, 'Brown sugar', 'White sugar with molasses or coconut sugar'),
(260, 25, 'Pandan leaves (optional)', 'Vanilla extract or banana leaves (for aroma)'),
(270, 28, '4 cups romaine lettuce (chopped)', 'Kale (massaged) or baby spinach'),
(271, 28, '1/2 cup Caesar dressing', 'Greek yogurt-based dressing, hummus with lemon, or a tahini-based dressing'),
(272, 28, '1/4 cup grated Parmesan cheese', 'Nutritional yeast, grated Pecorino Romano, or a vegan Parmesan substitute\r\n'),
(273, 28, '1 cup croutons', 'Toasted chickpeas, roasted nuts (like almonds or walnuts), or whole-grain crackers (crumbled)'),
(274, 28, '1 tbsp olive oil (optional for garnish)', 'Avocado oil or a dash of truffle oil'),
(275, 28, '1 tbsp lemon juice (optional)', 'Lime juice or a splash of white wine vinegar'),
(276, 29, 'Cucumber', 'Zucchini (thinly sliced) or green bell pepper'),
(277, 29, 'Cherry tomatoes', 'Diced red tomatoes or roasted red peppers'),
(278, 29, 'Red onion', 'Shallots or thinly sliced green onions'),
(279, 29, 'Kalamata olives', 'Black olives, green olives, or capers for a salty touch'),
(280, 29, 'Feta cheese', 'Goat cheese, crumbled ricotta salata, or a vegan feta alternative'),
(281, 29, 'Olive oil', 'Avocado oil or grapeseed oil'),
(282, 29, 'Lemon juice', 'Red wine vinegar or apple cider vinegar'),
(283, 29, 'Oregano', 'Thyme, basil, or mint'),
(284, 30, 'Fresh mozzarella', ' Burrata, goat cheese, or vegan mozzarella'),
(285, 30, 'Ripe tomatoes', 'Cherry tomatoes, heirloom tomatoes, or roasted red peppers'),
(286, 30, 'Fresh basil', ' Arugula, mint, or baby spinach'),
(287, 30, 'Olive oil', ' Avocado oil or walnut oil'),
(288, 30, 'Balsamic vinegar', 'Red wine vinegar, white balsamic, or a lemon vinaigrette'),
(289, 30, 'Salt', 'Sea salt flakes or Himalayan pink salt'),
(290, 30, 'Pepper', 'Crushed red pepper flakes or herbes de Provence'),
(311, 39, 'adasdas', 'asdasdasd'),
(312, 40, 'ascascas', 'ascascasc'),
(313, 41, 'ascascascas', 'ascascasc'),
(314, 41, 'cas', 'asas'),
(315, 41, 'casc', 'cas'),
(316, 41, 'as', 'casc'),
(317, 41, 'casc', 'asc'),
(318, 41, 'ascas', 'asc'),
(319, 41, 'casc', '');
>>>>>>> c7309906277cd27614d1627067c583d2e05402ca

-- --------------------------------------------------------

--
-- Table structure for table `instructions`
--

CREATE TABLE `instructions` (
  `instruction_id` int(11) NOT NULL,
  `meal_id` int(11) NOT NULL,
  `step_number` int(11) NOT NULL,
  `step_description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `instructions`
--

INSERT INTO `instructions` (`instruction_id`, `meal_id`, `step_number`, `step_description`) VALUES
(305, 19, 1, 'fsdfsdf'),
(306, 19, 2, 'gfsdfsd'),
(307, 19, 3, 'fsdfs'),
(308, 19, 4, 'df'),
(309, 19, 5, 'sf'),
(310, 19, 6, ''),
(311, 19, 7, ''),
(312, 19, 8, ''),
(313, 19, 9, ''),
(411, 25, 1, 'Cook the rice: Soak glutinous rice in water for a few hours. Drain and cook with coconut milk, brown sugar, and pandan leaves in a pot over low heat.'),
(412, 25, 2, 'Simmer: Stir occasionally until the liquid is absorbed and the rice becomes sticky and soft.'),
(413, 25, 3, 'Set and Serve: Transfer the cooked rice to a greased pan and let it cool. Cut into squares and serve warm or cold.'),
(423, 28, 1, 'Wash and chop the romaine lettuce into bite-sized pieces.'),
(424, 28, 2, 'Place the lettuce in a large bowl.'),
(425, 28, 3, 'Drizzle Caesar dressing over the lettuce and toss well.'),
(426, 28, 4, 'Add croutons and grated Parmesan cheese, tossing again to mix.'),
(427, 28, 5, 'Optional: Drizzle with olive oil and lemon juice for added flavor.'),
(428, 28, 6, 'Serve immediately as a side or starter.'),
(429, 29, 1, 'Toss chopped vegetables and olives in a bowl'),
(430, 29, 2, 'Drizzle with olive oil and lemon juice'),
(431, 29, 3, 'Sprinkle oregano'),
(432, 29, 4, 'top with feta cheese'),
(433, 29, 5, ''),
(434, 30, 1, 'Slice the mozzarella and tomatoes.'),
(435, 30, 2, 'Layer them alternately on a plate.'),
(436, 30, 3, 'Add fresh basil leaves between layers.'),
(437, 30, 4, 'Drizzle with olive oil and balsamic vinegar.'),
(438, 30, 5, 'Season with salt and pepper to taste.'),
<<<<<<< HEAD
(439, 31, 1, 'asdfasdf,asdfasd,adsfa,sdf'),
(440, 32, 1, 'asdcasdc'),
(441, 32, 2, 'asdcasd'),
(442, 32, 3, 'casdcascd'),
(443, 32, 4, 'ascasdcasdc'),
(444, 33, 1, 'ins1'),
(445, 33, 2, 'ins2'),
(446, 33, 3, 'ins3'),
(447, 33, 4, 'ins4');
=======
(442, 34, 1, 'dasdasdas'),
(443, 35, 1, 'dasdasdas'),
(444, 36, 1, 'asd'),
(445, 37, 1, 'asdasdas'),
(446, 38, 1, 'kill'),
(447, 39, 1, 'asdasdasdasdasdad'),
(448, 40, 1, 'ascasca'),
(449, 41, 1, 'ascascascas'),
(450, 42, 1, 'ascascascas');
>>>>>>> c7309906277cd27614d1627067c583d2e05402ca

-- --------------------------------------------------------

--
-- Table structure for table `meals`
--

CREATE TABLE `meals` (
  `meal_id` int(11) NOT NULL,
  `meal_name` varchar(255) NOT NULL,
  `category_id` int(11) NOT NULL,
  `video_link` varchar(255) DEFAULT NULL,
  `date_created` datetime NOT NULL,
  `username` varchar(255) NOT NULL,
  `description` varchar(50) NOT NULL,
  `views` int(11) DEFAULT 0,
  `where_buy` varchar(255) NOT NULL,
  `nutri_info` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `meals`
--

<<<<<<< HEAD
INSERT INTO `meals` (`meal_id`, `meal_name`, `category_id`, `video_link`, `date_created`, `username`, `description`, `views`, `where_buy`, `nutri_info`) VALUES
(19, 'Adobo', 5, 'https://youtu.be/mtyULaM6RfQ?si=gOzpJsXqiiF2EdmT', '2023-12-24 12:43:11', 'joanna', 'asdasdayduasa', 38, '', ''),
(25, 'Biko', 5, 'https://www.youtube.com/watch?v=eRngjbA-xGE', '2024-12-14 23:55:24', 'sydney', 'A sweet and sticky Filipino rice cake made with gl', 388, '', ''),
(28, 'Caesar Salad', 10, 'https://www.youtube.com/watch?v=a4Z2x0sPq3A', '2024-12-29 10:52:15', 'sydney', 'A classic, creamy salad made with crisp romaine le', 19, '', ''),
(29, 'Greek Salad', 10, 'https://www.youtube.com/watch?v=dDhOpHcAJGo', '2024-12-29 20:12:33', 'sydney', 'A refreshing Mediterranean salad with crisp vegeta', 7, '', ''),
(30, 'Caprese Salad', 10, 'https://www.youtube.com/watch?v=V4Cv_hVwT00', '2024-12-29 20:15:03', 'sydney', 'A simple Italian salad featuring fresh mozzarella,', 9, '', ''),
(31, 'Sinigang na malamig', 7, 'youtube.com', '2024-12-30 16:47:17', 'user', 'casdfasdf', 5, '', ''),
(32, 'asdfasc', 6, 'acsdfa', '2024-12-30 17:11:06', 'user', 'asdfasdf', 9, 'asasc', ''),
(33, 'Kanin na lamig', 6, 'asdf.com', '2024-12-30 18:13:25', 'user', 'acsdascddascasdrear', 87, 'Palengke', '');
=======
INSERT INTO `meals` (`meal_id`, `meal_name`, `category_id`, `video_link`, `date_created`, `username`, `description`, `views`) VALUES
(19, 'Adobo', 5, 'https://youtu.be/mtyULaM6RfQ?si=gOzpJsXqiiF2EdmT', '2023-12-24 12:43:11', 'joanna', 'asdasdayduasa', 38),
(25, 'Biko', 5, 'https://www.youtube.com/watch?v=eRngjbA-xGE', '2024-12-14 23:55:24', 'sydney', 'A sweet and sticky Filipino rice cake made with gl', 389),
(28, 'Caesar Salad', 10, 'https://www.youtube.com/watch?v=a4Z2x0sPq3A', '2024-12-29 10:52:15', 'sydney', 'A classic, creamy salad made with crisp romaine le', 20),
(29, 'Greek Salad', 10, 'https://www.youtube.com/watch?v=dDhOpHcAJGo', '2024-12-29 20:12:33', 'sydney', 'A refreshing Mediterranean salad with crisp vegeta', 36),
(30, 'Caprese Salad', 10, 'https://www.youtube.com/watch?v=V4Cv_hVwT00', '2024-12-29 20:15:03', 'sydney', 'A simple Italian salad featuring fresh mozzarella,', 48),
(34, 'LL', 6, 'asdasda', '2025-01-01 15:03:39', 'Aaron', 'dasdasdas', 0),
(35, 'LL', 6, 'asdasda', '2025-01-01 15:05:28', 'Aaron', 'dasdasdas', 1),
(36, 'adasdas', 5, 'asdasd', '2025-01-01 15:05:35', 'Aaron', 'adasdasd', 1),
(37, 'asdasd', 5, 'asdasdas', '2025-01-01 15:05:47', 'Aaron', 'asdasd', 1),
(38, 'HELLO', 5, 'gajhre', '2025-01-01 15:07:24', 'Aaron', 'gaming yeah', 2),
(39, 'sdasdasd', 5, 'asdsada', '2025-01-01 15:44:37', 'Aaron', 'adasdsa', 0),
(40, 'xcascdasc', 5, 'sacsacas', '2025-01-01 15:44:57', 'Aaron', 'ascasc', 1),
(41, 'scsacas', 5, 'ascsacas', '2025-01-01 15:45:22', 'Aaron', 'sacascasc', 0),
(42, 'scsacas', 5, 'ascsacas', '2025-01-01 15:48:23', 'Aaron', 'sacascasc', 0);
>>>>>>> c7309906277cd27614d1627067c583d2e05402ca

-- --------------------------------------------------------

--
-- Table structure for table `meal_images`
--

CREATE TABLE `meal_images` (
  `image_id` int(11) NOT NULL,
  `meal_id` int(11) DEFAULT NULL,
  `image_link` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `meal_images`
--

INSERT INTO `meal_images` (`image_id`, `meal_id`, `image_link`) VALUES
(28, 19, 'https://jooinn.com/images/lonely-tree-reflection-3.jpg'),
(39, 25, 'https://www.businessnews.com.ph/wp-content/uploads/2016/01/biko-1.jpg'),
(42, 28, 'https://www.marecette.ch/wp-content/uploads/2020/05/salade-cesar.jpg'),
(43, 29, 'https://food-images.files.bbci.co.uk/food/recipes/greek_salad_16407_16x9.jpg'),
(44, 30, 'https://www.modernhoney.com/wp-content/uploads/2021/07/Caprese-Salad-4-scaled.jpg'),
<<<<<<< HEAD
(45, 31, 'google.com/ai'),
(46, 32, 'asdfasc'),
(47, 33, 'https://cdn.loveandlemons.com/wp-content/uploads/2020/03/how-to-cook-rice.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `nutritional_info`
--

CREATE TABLE `nutritional_info` (
  `info_id` int(11) NOT NULL,
  `meal_id` int(11) NOT NULL,
  `nutrition_text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `nutritional_info`
--

INSERT INTO `nutritional_info` (`info_id`, `meal_id`, `nutrition_text`) VALUES
(1, 32, 'asdcasc'),
(2, 32, 'casdcasdca'),
(3, 32, 'sdcasdcasdc'),
(4, 32, 'cadscasdc'),
(5, 33, 'a'),
(6, 33, 'b'),
(7, 33, 'c'),
(8, 33, 'd'),
(9, 33, 'e');
=======
(48, 34, 'asdasda'),
(49, 35, 'asdasda'),
(50, 36, 'asdasdas'),
(51, 37, 'asdasd'),
(52, 38, 'efwef'),
(53, 40, 'cascascas'),
(54, 41, 'cascasc'),
(55, 42, 'cascasc');
>>>>>>> c7309906277cd27614d1627067c583d2e05402ca

-- --------------------------------------------------------

--
-- Table structure for table `ratings`
--

CREATE TABLE `ratings` (
  `rating_id` int(11) NOT NULL,
  `meal_id` int(11) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `rating_value` decimal(3,2) DEFAULT NULL,
  `date_rated` timestamp NOT NULL DEFAULT current_timestamp(),
  `rating_comment` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ratings`
--

INSERT INTO `ratings` (`rating_id`, `meal_id`, `username`, `rating_value`, `date_rated`, `rating_comment`) VALUES
(5, 19, 'cindyasp', 1.00, '2023-12-26 09:28:42', 'eww'),
(8, 19, 'joanna', 1.00, '2023-12-26 09:38:16', 'i burned my house'),
(10, 25, 'sydney', 3.00, '2024-12-14 18:17:17', 'hhehe');

-- --------------------------------------------------------

--
-- Table structure for table `testimonies`
--

CREATE TABLE `testimonies` (
  `testimony_id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `testimonial_text` text NOT NULL,
  `date_posted` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `testimonies`
--

INSERT INTO `testimonies` (`testimony_id`, `username`, `testimonial_text`, `date_posted`) VALUES
(1, 'joanna', 'Nagkajowa ako bi', '2023-12-26 08:41:50');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`username`, `email`, `password`) VALUES
('Aaron', 'lol@gmail.com', '$2y$10$D/ZagQ9hIegBnwu3k9UOpebrhWdq7jnlWWPFXhlmtqH2C1p.9sPDa'),
('admin', 'jareniego_21ur0123@psu.edu.ph', '$2y$10$hVYglgb/SdtVHmAcAsJpM.iBzXdjqu4AfClbYszeMq6LGV/y12CjC'),
('Cindy Marie', 'cindyasp1004@gmail.com', '$2y$10$AnhxpXu8smz/3N4BHFasbO1uGirTQuImc2wuO6abfHcxSK0E1qT/G'),
('cindyasp', 'joannamarieo.areniego@yahoo.com', '$2y$10$LbX/oXjXqx8DH2wP8gJL6u0VMS/0rdedXOvbwgrf.CAkorai8me/2'),
('joanna', 'joannamarieo.areniego@gmail.com', '$2y$10$q6szw9qqjvoteJIfwSxiteWbGk/14aP6mYRKSoV6xy7ye1YOeibRy'),
<<<<<<< HEAD
('sydney', 'sydneymae1004@gmail.com', '$2y$10$MXypmeU5Wh4xbPLt8untpOihe523zBM/GxFJC5LeBWyr5zn51VTy.'),
('user', 'user@g.com', '$2y$10$MzkXiayZirvyIEC.OIIW/u1lFRc53W9in1J2xRFgG3pU9LMYKDJvq');
=======
('sydney', 'sydneymae1004@gmail.com', '$2y$10$MXypmeU5Wh4xbPLt8untpOihe523zBM/GxFJC5LeBWyr5zn51VTy.');
>>>>>>> c7309906277cd27614d1627067c583d2e05402ca

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `chat_data`
--
ALTER TABLE `chat_data`
  ADD PRIMARY KEY (`id`),
  ADD KEY `username` (`username`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `comments_ibfk_1` (`meal_id`),
  ADD KEY `comments_ibfk_2` (`user_name`);

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`favorite_id`),
  ADD UNIQUE KEY `username` (`username`,`meal_id`),
  ADD KEY `meal_id` (`meal_id`);

--
-- Indexes for table `ingredients`
--
ALTER TABLE `ingredients`
  ADD PRIMARY KEY (`ingredient_id`),
  ADD KEY `ingredients_ibfk_1` (`meal_id`);

--
-- Indexes for table `instructions`
--
ALTER TABLE `instructions`
  ADD PRIMARY KEY (`instruction_id`),
  ADD KEY `instructions_ibfk_1` (`meal_id`);

--
-- Indexes for table `meals`
--
ALTER TABLE `meals`
  ADD PRIMARY KEY (`meal_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `username` (`username`);

--
-- Indexes for table `meal_images`
--
ALTER TABLE `meal_images`
  ADD PRIMARY KEY (`image_id`),
  ADD KEY `meal_images_ibfk_1` (`meal_id`);

--
-- Indexes for table `nutritional_info`
--
ALTER TABLE `nutritional_info`
  ADD PRIMARY KEY (`info_id`),
  ADD KEY `fk_meal` (`meal_id`);

--
-- Indexes for table `ratings`
--
ALTER TABLE `ratings`
  ADD PRIMARY KEY (`rating_id`),
  ADD KEY `meal_id` (`meal_id`),
  ADD KEY `username` (`username`);

--
-- Indexes for table `testimonies`
--
ALTER TABLE `testimonies`
  ADD PRIMARY KEY (`testimony_id`),
  ADD KEY `username` (`username`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `chat_data`
--
ALTER TABLE `chat_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=222;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `favorites`
--
ALTER TABLE `favorites`
<<<<<<< HEAD
  MODIFY `favorite_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;
=======
  MODIFY `favorite_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;
>>>>>>> c7309906277cd27614d1627067c583d2e05402ca

--
-- AUTO_INCREMENT for table `ingredients`
--
ALTER TABLE `ingredients`
<<<<<<< HEAD
  MODIFY `ingredient_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=299;
=======
  MODIFY `ingredient_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=341;
>>>>>>> c7309906277cd27614d1627067c583d2e05402ca

--
-- AUTO_INCREMENT for table `instructions`
--
ALTER TABLE `instructions`
<<<<<<< HEAD
  MODIFY `instruction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=448;
=======
  MODIFY `instruction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=455;
>>>>>>> c7309906277cd27614d1627067c583d2e05402ca

--
-- AUTO_INCREMENT for table `meals`
--
ALTER TABLE `meals`
<<<<<<< HEAD
  MODIFY `meal_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;
=======
  MODIFY `meal_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;
>>>>>>> c7309906277cd27614d1627067c583d2e05402ca

--
-- AUTO_INCREMENT for table `meal_images`
--
ALTER TABLE `meal_images`
<<<<<<< HEAD
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `nutritional_info`
--
ALTER TABLE `nutritional_info`
  MODIFY `info_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
=======
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;
>>>>>>> c7309906277cd27614d1627067c583d2e05402ca

--
-- AUTO_INCREMENT for table `ratings`
--
ALTER TABLE `ratings`
  MODIFY `rating_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `testimonies`
--
ALTER TABLE `testimonies`
  MODIFY `testimony_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `chat_data`
--
ALTER TABLE `chat_data`
  ADD CONSTRAINT `chat_data_ibfk_1` FOREIGN KEY (`username`) REFERENCES `users` (`username`);

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`meal_id`) REFERENCES `meals` (`meal_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_name`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`username`) REFERENCES `users` (`username`),
  ADD CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`meal_id`) REFERENCES `meals` (`meal_id`);

--
-- Constraints for table `ingredients`
--
ALTER TABLE `ingredients`
  ADD CONSTRAINT `ingredients_ibfk_1` FOREIGN KEY (`meal_id`) REFERENCES `meals` (`meal_id`) ON DELETE CASCADE;

--
-- Constraints for table `instructions`
--
ALTER TABLE `instructions`
  ADD CONSTRAINT `instructions_ibfk_1` FOREIGN KEY (`meal_id`) REFERENCES `meals` (`meal_id`) ON DELETE CASCADE;

--
-- Constraints for table `meals`
--
ALTER TABLE `meals`
  ADD CONSTRAINT `meals_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`),
  ADD CONSTRAINT `meals_ibfk_2` FOREIGN KEY (`username`) REFERENCES `users` (`username`);

--
-- Constraints for table `meal_images`
--
ALTER TABLE `meal_images`
  ADD CONSTRAINT `meal_images_ibfk_1` FOREIGN KEY (`meal_id`) REFERENCES `meals` (`meal_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `ratings`
--
ALTER TABLE `ratings`
  ADD CONSTRAINT `ratings_ibfk_1` FOREIGN KEY (`meal_id`) REFERENCES `meals` (`meal_id`),
  ADD CONSTRAINT `ratings_ibfk_2` FOREIGN KEY (`username`) REFERENCES `users` (`username`);

--
-- Constraints for table `testimonies`
--
ALTER TABLE `testimonies`
  ADD CONSTRAINT `testimonies_ibfk_1` FOREIGN KEY (`username`) REFERENCES `users` (`username`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
