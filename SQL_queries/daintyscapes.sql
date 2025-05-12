-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 12, 2025 at 11:23 AM
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
-- Database: `daintyscapes`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `add_buyer` (IN `p_username` VARCHAR(50), IN `p_password_hash` VARCHAR(100), IN `p_email` VARCHAR(50), IN `p_phone_number` VARCHAR(17), IN `p_country` VARCHAR(50), IN `p_city` VARCHAR(50), IN `p_barangay` VARCHAR(50), IN `p_house_number` VARCHAR(20), IN `p_postal_code` VARCHAR(10))   BEGIN
	DECLARE buyer_uid INT;

	START TRANSACTION;

	INSERT INTO users (username, password_hash, role)
	VALUES (p_username, p_password_hash, 'buyer');

	-- Get the last inserted ID into local variable
	SET buyer_uid = LAST_INSERT_ID();
    
	INSERT INTO buyers (user_id, email, phone_number)
	VALUES (buyer_uid, p_email, p_phone_number);

	INSERT INTO addresses (buyer_id, country, city, barangay, house_number, postal_code)
    VALUES (buyer_uid, p_country, p_city, p_barangay, p_house_number, p_postal_code);

	COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `add_product` (IN `p_product_category_name` TEXT, IN `p_product_name` VARCHAR(100), IN `p_product_color` VARCHAR(50), IN `p_available_quantity` INT, IN `p_base_price` DECIMAL(19,4), IN `p_image_url` VARCHAR(255))   BEGIN
    DECLARE fk_category_id INT;

    START TRANSACTION;
        SELECT category_id INTO fk_category_id
        FROM product_categories 
        WHERE category_name = p_product_category_name
        LIMIT 1;

        IF fk_category_id IS NULL THEN
            -- Optionally, insert the category if it doesn't exist
            INSERT INTO product_categories (category_name) VALUES (p_product_category_name);
            SET fk_category_id = LAST_INSERT_ID();
        END IF;

        INSERT INTO products (category_id, product_name, product_color, available_quantity, base_price, image_url)
        VALUES (fk_category_id, p_product_name, p_product_color, p_available_quantity, p_base_price, p_image_url);

    COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `add_seller` (IN `p_username` VARCHAR(50), IN `p_password_hash` VARCHAR(100))   BEGIN
	DECLARE uid INT;

	START TRANSACTION;

	INSERT INTO users (username, password_hash, role)
	VALUES (p_username, p_password_hash, 'seller');

	-- Get the last inserted ID into local variable
	SET uid = LAST_INSERT_ID();

	INSERT INTO seller (user_id)
	VALUES (uid);

	COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `modify_product` (IN `p_product_id` INT, IN `p_product_category_name` TEXT, IN `p_product_name` VARCHAR(100), IN `p_product_color` VARCHAR(50), IN `p_available_quantity` INT, IN `p_base_price` DECIMAL(19,4), IN `p_image_url` VARCHAR(255))   BEGIN
    DECLARE fk_category_id INT;

    START TRANSACTION;
        SELECT category_id INTO fk_category_id
        FROM product_categories 
        WHERE category_name = p_product_category_name
        LIMIT 1;

        IF fk_category_id IS NULL THEN
            INSERT INTO product_categories (category_name) VALUES (p_product_category_name);
            SET fk_category_id = LAST_INSERT_ID();
        END IF;

        UPDATE products
        SET category_id = fk_category_id,
            product_name = p_product_name,
            product_color = p_product_color,
            available_quantity = p_available_quantity,
            base_price = p_base_price,
            image_url = p_image_url
        WHERE product_id = p_product_id;

    COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `update_buyer` (IN `p_username` VARCHAR(50), IN `p_email` VARCHAR(50), IN `p_phone_number` VARCHAR(17), IN `p_country` VARCHAR(50), IN `p_city` VARCHAR(50), IN `p_barangay` VARCHAR(50), IN `p_house_number` VARCHAR(20), IN `p_postal_code` VARCHAR(10))   BEGIN
    DECLARE v_user_id INT;

    START TRANSACTION;

    -- Get user_id from username
    SELECT user_id INTO v_user_id FROM users WHERE username = p_username;

    -- Update buyers table
    UPDATE buyers
    SET email = p_email,
        phone_number = p_phone_number
    WHERE user_id = v_user_id;

    -- Update addresses table (if exists, else insert)
    IF EXISTS (SELECT 1 FROM addresses WHERE buyer_id = v_user_id) THEN
        UPDATE addresses
        SET country = p_country,
            city = p_city,
            barangay = p_barangay,
            house_number = p_house_number,
            postal_code = p_postal_code
        WHERE buyer_id = v_user_id;
    ELSE
        INSERT INTO addresses (buyer_id, country, city, barangay, house_number, postal_code)
        VALUES (v_user_id, p_country, p_city, p_barangay, p_house_number, p_postal_code);
    END IF;

    COMMIT;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

CREATE TABLE `addresses` (
  `address_id` int(11) NOT NULL,
  `buyer_id` int(11) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `barangay` varchar(50) DEFAULT NULL,
  `house_number` varchar(20) DEFAULT NULL,
  `postal_code` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `addresses`
--

INSERT INTO `addresses` (`address_id`, `buyer_id`, `country`, `city`, `barangay`, `house_number`, `postal_code`) VALUES
(2, 2, 'Philippines', 'Malvar', 'San Pedro 1sad', 'n/a', '4233'),
(5, 6, 'Philippines', 'Malvar', 'asdf', 'asdf', '4233');

-- --------------------------------------------------------

--
-- Table structure for table `buyers`
--

CREATE TABLE `buyers` (
  `buyer_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `phone_number` varchar(17) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `buyers`
--

INSERT INTO `buyers` (`buyer_id`, `user_id`, `email`, `phone_number`) VALUES
(2, 2, 'tite@gmail.com', '09562672240'),
(6, 6, 'ventralberry@gmail.com', '09562672241');

-- --------------------------------------------------------

--
-- Table structure for table `charms`
--

CREATE TABLE `charms` (
  `charm_id` int(11) NOT NULL,
  `charm_name` varchar(30) DEFAULT NULL,
  `charm_base_price` decimal(19,4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customizations`
--

CREATE TABLE `customizations` (
  `customization_id` int(11) NOT NULL,
  `buyer_id` int(11) DEFAULT NULL,
  `customized_name` varchar(20) DEFAULT NULL,
  `customized_name_color` varchar(50) DEFAULT NULL,
  `customization_cost` decimal(19,4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customization_charms`
--

CREATE TABLE `customization_charms` (
  `customization_charm_id` int(11) NOT NULL,
  `customization_id` int(11) DEFAULT NULL,
  `charm_id` int(11) DEFAULT NULL,
  `x_position` int(11) DEFAULT NULL,
  `y_position` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `buyer_id` int(11) DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `order_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_details`
--

CREATE TABLE `order_details` (
  `order_detail_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `customization_id` int(11) DEFAULT NULL,
  `order_quantity` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_status`
--

CREATE TABLE `order_status` (
  `status_id` int(11) NOT NULL,
  `status_name` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `product_name` varchar(100) DEFAULT NULL,
  `product_color` varchar(50) DEFAULT NULL,
  `available_quantity` int(11) DEFAULT NULL,
  `base_price` decimal(19,4) DEFAULT NULL,
  `image_url` varchar(1024) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `category_id`, `product_name`, `product_color`, `available_quantity`, `base_price`, `image_url`) VALUES
(2, 3, 'Zombie Hamburglar', 'Green', 123, 123.0000, 'https://venb.ddns.net/gallery/IMG_20250512_010923_069.jpg'),
(4, 3, 'Birdie Wings', 'Yellow', 123, 123.0000, 'https://venb.ddns.net/gallery/received_708802188248720.jpeg'),
(5, 3, 'Soda Potion', 'Orange', 123, 123.0000, 'https://venb.ddns.net/gallery/received_1100308605190148.jpeg');

-- --------------------------------------------------------

--
-- Table structure for table `product_categories`
--

CREATE TABLE `product_categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_categories`
--

INSERT INTO `product_categories` (`category_id`, `category_name`) VALUES
(1, 'asdf'),
(2, 'asdfasdf'),
(3, 'Toy');

-- --------------------------------------------------------

--
-- Table structure for table `seller`
--

CREATE TABLE `seller` (
  `seller_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `seller`
--

INSERT INTO `seller` (`seller_id`, `user_id`) VALUES
(1, 7);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(100) NOT NULL,
  `role` enum('buyer','seller','admin') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `first_name`, `last_name`, `username`, `password_hash`, `role`) VALUES
(2, NULL, NULL, 'buyer1', '$2y$10$PjJpqjI2l57bvY7OrSWI2O2b3QVRo4jPb3hPFbyKz9QHQnMETD.mG', 'buyer'),
(5, NULL, NULL, 'admin', '$2y$10$TTjFXRqqiHWg6OMpvtS7ruXU4Fa08XyUxuwLCuT4M626JXfAA7Bgy', 'admin'),
(6, NULL, NULL, 'buyer2', '$2y$10$q2XG5KK76.2LMB/302dwL.eraWgELn9iUNCRcZjYgHnIv1A/5uPsC', 'buyer'),
(7, NULL, NULL, 'seller1', '$2y$10$1ZIoLMFC1LbwlYJeTiT.7.U8.xp2HYF33ZOF3HDZMmVizjG5MVAZy', 'seller');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`address_id`),
  ADD KEY `buyer_id` (`buyer_id`);

--
-- Indexes for table `buyers`
--
ALTER TABLE `buyers`
  ADD PRIMARY KEY (`buyer_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `phone_number` (`phone_number`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `charms`
--
ALTER TABLE `charms`
  ADD PRIMARY KEY (`charm_id`);

--
-- Indexes for table `customizations`
--
ALTER TABLE `customizations`
  ADD PRIMARY KEY (`customization_id`);

--
-- Indexes for table `customization_charms`
--
ALTER TABLE `customization_charms`
  ADD PRIMARY KEY (`customization_charm_id`),
  ADD KEY `customization_id` (`customization_id`),
  ADD KEY `charm_id` (`charm_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `buyer_id` (`buyer_id`),
  ADD KEY `status_id` (`status_id`);

--
-- Indexes for table `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`order_detail_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `customization_id` (`customization_id`);

--
-- Indexes for table `order_status`
--
ALTER TABLE `order_status`
  ADD PRIMARY KEY (`status_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `product_categories`
--
ALTER TABLE `product_categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `seller`
--
ALTER TABLE `seller`
  ADD PRIMARY KEY (`seller_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addresses`
--
ALTER TABLE `addresses`
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `buyers`
--
ALTER TABLE `buyers`
  MODIFY `buyer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `charms`
--
ALTER TABLE `charms`
  MODIFY `charm_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customizations`
--
ALTER TABLE `customizations`
  MODIFY `customization_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customization_charms`
--
ALTER TABLE `customization_charms`
  MODIFY `customization_charm_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_details`
--
ALTER TABLE `order_details`
  MODIFY `order_detail_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_status`
--
ALTER TABLE `order_status`
  MODIFY `status_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `product_categories`
--
ALTER TABLE `product_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `seller`
--
ALTER TABLE `seller`
  MODIFY `seller_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `addresses_ibfk_1` FOREIGN KEY (`buyer_id`) REFERENCES `buyers` (`buyer_id`);

--
-- Constraints for table `buyers`
--
ALTER TABLE `buyers`
  ADD CONSTRAINT `buyers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `customization_charms`
--
ALTER TABLE `customization_charms`
  ADD CONSTRAINT `customization_charms_ibfk_1` FOREIGN KEY (`customization_id`) REFERENCES `customizations` (`customization_id`),
  ADD CONSTRAINT `customization_charms_ibfk_2` FOREIGN KEY (`charm_id`) REFERENCES `charms` (`charm_id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`buyer_id`) REFERENCES `buyers` (`buyer_id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`status_id`) REFERENCES `order_status` (`status_id`);

--
-- Constraints for table `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`),
  ADD CONSTRAINT `order_details_ibfk_3` FOREIGN KEY (`customization_id`) REFERENCES `customizations` (`customization_id`);

--
-- Constraints for table `seller`
--
ALTER TABLE `seller`
  ADD CONSTRAINT `seller_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
