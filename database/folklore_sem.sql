-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 15, 2025 at 08:47 AM
-- Server version: 10.4.22-MariaDB
-- PHP Version: 8.0.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `folklore_sem`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `display_name` varchar(50) NOT NULL,
  `question_text` varchar(255) NOT NULL,
  `is_required` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `name`, `display_name`, `question_text`, `is_required`) VALUES
(1, 'main_ingredient', 'Main Ingredient', 'What is your preferred main ingredient?', 1),
(2, 'protein', 'Protein', 'What protein would you like?', 1),
(3, 'vegetables', 'Vegetables', 'What vegetables would you like?', 1),
(4, 'spice_level', 'Spice Level', 'How spicy would you like your food?', 1);

-- --------------------------------------------------------

--
-- Table structure for table `category_options`
--

CREATE TABLE `category_options` (
  `option_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `display_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `category_options`
--

INSERT INTO `category_options` (`option_id`, `category_id`, `name`, `display_name`) VALUES
(1, 1, 'rice', 'Rice'),
(2, 1, 'pasta', 'Pasta'),
(3, 1, 'bread', 'Bread'),
(4, 1, 'potato', 'Potato'),
(5, 2, 'chicken', 'Chicken'),
(6, 2, 'beef', 'Beef'),
(7, 2, 'pork', 'Pork'),
(8, 2, 'tofu', 'Tofu'),
(9, 2, 'fish', 'Fish'),
(10, 3, 'mixed_vegetables', 'Mixed Vegetables'),
(11, 3, 'leafy_greens', 'Leafy Greens'),
(12, 3, 'none', 'No Vegetables'),
(13, 3, 'root_vegetables', 'Root Vegetables'),
(14, 4, 'mild', 'Mild'),
(15, 4, 'medium', 'Medium'),
(16, 4, 'spicy', 'Spicy'),
(17, 4, 'very_spicy', 'Very Spicy');

-- --------------------------------------------------------

--
-- Table structure for table `default_recommendations`
--

CREATE TABLE `default_recommendations` (
  `recommendation_id` int(11) NOT NULL,
  `recipe_id` int(11) NOT NULL,
  `option_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`option_ids`)),
  `priority` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `default_recommendations`
--

INSERT INTO `default_recommendations` (`recommendation_id`, `recipe_id`, `option_ids`, `priority`) VALUES
(1, 1, '[2, 6, 12, 15]', 1),
(2, 2, '[1, 5, 10, 14]', 1),
(3, 3, '[1, 5, 10, 16]', 1),
(4, 4, '[3, 12, 14]', 1),
(5, 5, '[3, 5, 11, 14]', 1);

-- --------------------------------------------------------

--
-- Table structure for table `recipes`
--

CREATE TABLE `recipes` (
  `recipe_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `ingredients` text NOT NULL,
  `instructions` text DEFAULT NULL,
  `prep_time` int(11) DEFAULT NULL,
  `cook_time` int(11) DEFAULT NULL,
  `servings` int(11) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `recipes`
--

INSERT INTO `recipes` (`recipe_id`, `name`, `description`, `ingredients`, `instructions`, `prep_time`, `cook_time`, `servings`, `image_url`, `created_at`) VALUES
(1, 'Spaghetti Bolognese', 'Classic Italian pasta dish with rich meat sauce', 'spaghetti, beef, tomato sauce, onion, garlic', '1. Boil spaghetti. 2. Cook beef with onion and garlic. 3. Add tomato sauce. 4. Combine and serve.', 20, 30, 4, '', '2025-05-15 06:38:08'),
(2, 'Fried Rice', 'Traditional Asian-style fried rice with vegetables and protein', 'rice, egg, soy sauce, chicken, peas', '1. Fry egg. 2. Add rice and chicken. 3. Mix peas and soy sauce. 4. Stir well and serve.', 20, 30, 4, '', '2025-05-15 06:38:08'),
(3, 'Chicken Curry', 'Flavorful curry with tender chicken and aromatic spices', 'chicken, curry powder, coconut milk, onion, garlic', '1. Cook onion and garlic. 2. Add chicken and curry powder. 3. Pour coconut milk. 4. Simmer until cooked.', 20, 30, 4, '', '2025-05-15 06:38:08'),
(4, 'Pancakes', 'Fluffy pancakes perfect for breakfast or brunch', 'flour, egg, milk, sugar, butter', '1. Mix ingredients. 2. Cook on pan until golden.', 20, 30, 4, '', '2025-05-15 06:38:08'),
(5, 'Caesar Salad', 'Classic salad with crisp lettuce, croutons, and creamy dressing', 'lettuce, chicken, croutons, parmesan, caesar dressing', '1. Chop lettuce. 2. Add chicken and croutons. 3. Sprinkle parmesan. 4. Toss with dressing.', 20, 30, 4, '', '2025-05-15 06:38:08');

-- --------------------------------------------------------

--
-- Table structure for table `recipe_options`
--

CREATE TABLE `recipe_options` (
  `recipe_option_id` int(11) NOT NULL,
  `recipe_id` int(11) NOT NULL,
  `option_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `recipe_options`
--

INSERT INTO `recipe_options` (`recipe_option_id`, `recipe_id`, `option_id`) VALUES
(1, 1, 2),
(2, 1, 6),
(3, 1, 12),
(4, 1, 15),
(5, 2, 1),
(6, 2, 5),
(7, 2, 10),
(8, 2, 14),
(9, 3, 1),
(10, 3, 5),
(11, 3, 10),
(12, 3, 16),
(13, 4, 3),
(14, 4, 12),
(15, 4, 14),
(16, 5, 3),
(17, 5, 5),
(18, 5, 11),
(19, 5, 14);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `category_options`
--
ALTER TABLE `category_options`
  ADD PRIMARY KEY (`option_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `default_recommendations`
--
ALTER TABLE `default_recommendations`
  ADD PRIMARY KEY (`recommendation_id`),
  ADD KEY `recipe_id` (`recipe_id`);

--
-- Indexes for table `recipes`
--
ALTER TABLE `recipes`
  ADD PRIMARY KEY (`recipe_id`);

--
-- Indexes for table `recipe_options`
--
ALTER TABLE `recipe_options`
  ADD PRIMARY KEY (`recipe_option_id`),
  ADD KEY `recipe_id` (`recipe_id`),
  ADD KEY `option_id` (`option_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `category_options`
--
ALTER TABLE `category_options`
  MODIFY `option_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `default_recommendations`
--
ALTER TABLE `default_recommendations`
  MODIFY `recommendation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `recipes`
--
ALTER TABLE `recipes`
  MODIFY `recipe_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `recipe_options`
--
ALTER TABLE `recipe_options`
  MODIFY `recipe_option_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `category_options`
--
ALTER TABLE `category_options`
  ADD CONSTRAINT `category_options_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`);

--
-- Constraints for table `default_recommendations`
--
ALTER TABLE `default_recommendations`
  ADD CONSTRAINT `default_recommendations_ibfk_1` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`recipe_id`);

--
-- Constraints for table `recipe_options`
--
ALTER TABLE `recipe_options`
  ADD CONSTRAINT `recipe_options_ibfk_1` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`recipe_id`),
  ADD CONSTRAINT `recipe_options_ibfk_2` FOREIGN KEY (`option_id`) REFERENCES `category_options` (`option_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
