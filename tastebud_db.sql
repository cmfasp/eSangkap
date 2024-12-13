-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 28, 2023 at 02:32 AM
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
(5, 'Dessert');

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

-- --------------------------------------------------------

--
-- Table structure for table `ingredients`
--

CREATE TABLE `ingredients` (
  `ingredient_id` int(11) NOT NULL,
  `meal_id` int(11) NOT NULL,
  `ingredient_name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ingredients`
--

INSERT INTO `ingredients` (`ingredient_id`, `meal_id`, `ingredient_name`) VALUES
(135, 14, 'gsdgfsdg'),
(136, 14, 'sdguiorge'),
(137, 14, 'glk89t'),
(153, 15, 'dsdaw'),
(154, 15, 'edasdad'),
(155, 15, ''),
(180, 19, 'fsdfas'),
(181, 19, 'sds'),
(182, 19, 'ds'),
(183, 19, 'fsd'),
(184, 19, 'gfd'),
(185, 19, 'gf'),
(186, 19, ''),
(187, 19, ''),
(188, 19, ''),
(189, 19, '');

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
(263, 14, 1, 'fdsfsa'),
(264, 14, 2, 'fdsfse'),
(265, 14, 3, 'fsfsd'),
(281, 15, 1, 'dasdsa'),
(282, 15, 2, 'dasdasd'),
(283, 15, 3, ''),
(305, 19, 1, 'fsdfsdf'),
(306, 19, 2, 'gfsdfsd'),
(307, 19, 3, 'fsdfs'),
(308, 19, 4, 'df'),
(309, 19, 5, 'sf'),
(310, 19, 6, ''),
(311, 19, 7, ''),
(312, 19, 8, ''),
(313, 19, 9, '');

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
  `views` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `meals`
--

INSERT INTO `meals` (`meal_id`, `meal_name`, `category_id`, `video_link`, `date_created`, `username`, `description`, `views`) VALUES
(14, 'Adobo', 5, 'https://youtu.be/mtyULaM6RfQ?si=gOzpJsXqiiF2EdmT', '2023-12-24 10:59:29', 'admin', 'sfafSDV', 0),
(15, 'ADOBO', 5, 'https://youtu.be/mtyULaM6RfQ?si=gOzpJsXqiiF2EdmT', '2023-12-24 11:17:01', 'admin', 'SDADASFHJADAFF ZSFF', 32),
(19, 'Adobo', 5, 'https://youtu.be/mtyULaM6RfQ?si=gOzpJsXqiiF2EdmT', '2023-12-24 12:43:11', 'joanna', 'asdasdayduasa', 28);

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
(1, 14, 'https://th.bing.com/th/id/OIP.gacOQOPc2Y6kZpsGBe6kKwHaFT?rs=1&pid=ImgDetMain'),
(16, 15, 'https://th.bing.com/th/id/OIP.XwmxAU5-1dw09gd-HDgxtAHaE8?rs=1&pid=ImgDetMain'),
(17, 15, 'https://th.bing.com/th/id/OIP.XwmxAU5-1dw09gd-HDgxtAHaE8?rs=1&pid=ImgDetMain'),
(28, 19, 'https://jooinn.com/images/lonely-tree-reflection-3.jpg');

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
(8, 19, 'joanna', 1.00, '2023-12-26 09:38:16', 'i burned my house');

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
('admin', 'jareniego_21ur0123@psu.edu.ph', '$2y$10$hVYglgb/SdtVHmAcAsJpM.iBzXdjqu4AfClbYszeMq6LGV/y12CjC'),
('cindyasp', 'joannamarieo.areniego@yahoo.com', '$2y$10$LbX/oXjXqx8DH2wP8gJL6u0VMS/0rdedXOvbwgrf.CAkorai8me/2'),
('joanna', 'joannamarieo.areniego@gmail.com', '$2y$10$q6szw9qqjvoteJIfwSxiteWbGk/14aP6mYRKSoV6xy7ye1YOeibRy');

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
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `chat_data`
--
ALTER TABLE `chat_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=220;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `ingredients`
--
ALTER TABLE `ingredients`
  MODIFY `ingredient_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=190;

--
-- AUTO_INCREMENT for table `instructions`
--
ALTER TABLE `instructions`
  MODIFY `instruction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=314;

--
-- AUTO_INCREMENT for table `meals`
--
ALTER TABLE `meals`
  MODIFY `meal_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `meal_images`
--
ALTER TABLE `meal_images`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `ratings`
--
ALTER TABLE `ratings`
  MODIFY `rating_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
