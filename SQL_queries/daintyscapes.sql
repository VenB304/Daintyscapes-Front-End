-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 13, 2025 at 04:19 PM
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
CREATE DEFINER=`root`@`localhost` PROCEDURE `add_buyer` (IN `p_first_name` VARCHAR(50), IN `p_last_name` VARCHAR(50), IN `p_username` VARCHAR(50), IN `p_password_hash` VARCHAR(100), IN `p_email` VARCHAR(50), IN `p_phone_number` VARCHAR(17), IN `p_country` VARCHAR(50), IN `p_city` VARCHAR(50), IN `p_barangay` VARCHAR(50), IN `p_house_number` VARCHAR(20), IN `p_postal_code` VARCHAR(10))   BEGIN
    DECLARE v_user_id INT;
    DECLARE v_buyer_id INT;

    START TRANSACTION;

    INSERT INTO users (first_name, last_name, username, password_hash, role)
    VALUES (p_first_name, p_last_name, p_username, p_password_hash, 'buyer');
    SET v_user_id = LAST_INSERT_ID();

    INSERT INTO buyers (user_id, email, phone_number)
    VALUES (v_user_id, p_email, p_phone_number);
    SET v_buyer_id = LAST_INSERT_ID();

    INSERT INTO addresses (buyer_id, country, city, barangay, house_number, postal_code)
    VALUES (v_buyer_id, p_country, p_city, p_barangay, p_house_number, p_postal_code);

    COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `add_product` (IN `p_product_category_name` TEXT, IN `p_product_name` VARCHAR(100), IN `p_available_quantity` INT, IN `p_base_price` DECIMAL(19,4))   BEGIN
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

        INSERT INTO products (category_id, product_name, available_quantity, base_price)
        VALUES (fk_category_id, p_product_name, p_available_quantity, p_base_price);

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

CREATE DEFINER=`root`@`localhost` PROCEDURE `delete_buyer` (IN `p_buyer_id` INT)   BEGIN
    DECLARE v_user_id INT;

    START TRANSACTION;
        -- Get user_id for this buyer
        SELECT user_id INTO v_user_id FROM buyers WHERE buyer_id = p_buyer_id;

        -- Delete from addresses first (by buyer_id)
        DELETE FROM addresses WHERE buyer_id = p_buyer_id;

        -- Now delete from buyers
        DELETE FROM buyers WHERE buyer_id = p_buyer_id;

        -- Optionally, delete from users if you want to fully remove the user
        DELETE FROM users WHERE user_id = v_user_id;
    COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `get_buyer_orders` (IN `p_username` VARCHAR(50))   BEGIN
    -- In your get_buyer_orders procedure:
SELECT
    o.order_id,
    o.order_date AS date,
    os.status_name AS status,
    od.product_id,
    p.product_name AS name,
    od.color_name,
    (SELECT image_url FROM product_colors WHERE product_id = p.product_id AND color_name = od.color_name LIMIT 1) AS image,
    od.order_quantity AS quantity,
    p.base_price AS price
FROM orders o
JOIN order_details od ON o.order_id = od.order_id
JOIN products p ON od.product_id = p.product_id
LEFT JOIN order_status os ON o.status_id = os.status_id
WHERE o.buyer_id = (SELECT b.buyer_id FROM buyers b JOIN users u ON b.user_id = u.user_id WHERE u.username = p_username)
ORDER BY o.order_date DESC, o.order_id DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `get_buyer_profile_by_username` (IN `p_username` VARCHAR(50))   BEGIN
    SELECT 
        u.username, 
        u.first_name,
        u.last_name,
        b.email, 
        b.phone_number, 
        a.country, 
        a.city, 
        a.postal_code, 
        a.barangay, 
        a.house_number
    FROM users u
    JOIN buyers b ON u.user_id = b.user_id
    LEFT JOIN addresses a ON b.buyer_id = a.buyer_id
    WHERE u.username = p_username
    LIMIT 1;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `get_products` (IN `p_search` VARCHAR(100), IN `p_min_price` DECIMAL(19,4), IN `p_max_price` DECIMAL(19,4), IN `p_sort` VARCHAR(20), IN `p_category_id` INT)   BEGIN
    SELECT 
        p.product_id AS id,
        p.product_name AS name,
        p.base_price,
        p.available_quantity,
        (SELECT image_url FROM product_colors WHERE product_id = p.product_id ORDER BY color_id ASC LIMIT 1) AS image
    FROM products p
    WHERE
        (p_search IS NULL OR p.product_name LIKE CONCAT('%', p_search, '%'))
        AND (p_min_price IS NULL OR p.base_price >= p_min_price)
        AND (p_max_price IS NULL OR p.base_price <= p_max_price)
        AND (p_category_id IS NULL OR p.category_id = p_category_id)
    ORDER BY
        CASE 
            WHEN p_sort = 'price_asc' THEN p.base_price
            WHEN p_sort = 'price_desc' THEN -p.base_price
            WHEN p_sort = 'oldest' THEN p.product_id
            ELSE -p.product_id
        END ASC,
        p.product_id DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `get_product_by_id` (IN `p_product_id` INT)   BEGIN
    SELECT 
        p.product_id, 
        pc.category_name, 
        p.product_name, 
        p.available_quantity, 
        p.base_price
    FROM products p
    LEFT JOIN product_categories pc ON p.category_id = pc.category_id
    WHERE p.product_id = p_product_id
    LIMIT 1;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `modify_product` (IN `p_product_id` INT, IN `p_product_category_name` TEXT, IN `p_product_name` VARCHAR(100), IN `p_product_color` VARCHAR(50), IN `p_available_quantity` INT, IN `p_base_price` DECIMAL(19,4))   BEGIN
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
            available_quantity = p_available_quantity,
            base_price = p_base_price
        WHERE product_id = p_product_id;

    COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `update_buyer` (IN `p_username` VARCHAR(50), IN `p_first_name` VARCHAR(50), IN `p_last_name` VARCHAR(50), IN `p_email` VARCHAR(50), IN `p_phone_number` VARCHAR(17), IN `p_country` VARCHAR(50), IN `p_city` VARCHAR(50), IN `p_barangay` VARCHAR(50), IN `p_house_number` VARCHAR(20), IN `p_postal_code` VARCHAR(10))   BEGIN
    DECLARE v_user_id INT;
    DECLARE v_buyer_id INT;

    -- Get user_id
    SELECT user_id INTO v_user_id FROM users WHERE username = p_username;
    -- Get buyer_id
    SELECT buyer_id INTO v_buyer_id FROM buyers WHERE user_id = v_user_id;

    -- Update users table
    UPDATE users
    SET first_name = p_first_name, last_name = p_last_name
    WHERE user_id = v_user_id;

    -- Update buyers table
    UPDATE buyers
    SET email = p_email, phone_number = p_phone_number
    WHERE buyer_id = v_buyer_id;

    -- Update addresses table (if exists, else insert)
    IF EXISTS (SELECT 1 FROM addresses WHERE buyer_id = v_buyer_id) THEN
        UPDATE addresses
        SET country = p_country,
            city = p_city,
            barangay = p_barangay,
            house_number = p_house_number,
            postal_code = p_postal_code
        WHERE buyer_id = v_buyer_id;
    ELSE
        INSERT INTO addresses (buyer_id, country, city, barangay, house_number, postal_code)
        VALUES (v_buyer_id, p_country, p_city, p_barangay, p_house_number, p_postal_code);
    END IF;
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
(1, 1, 'Philippines', 'Malvar', 'San Pedro 1', 'n/a', '4233');

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
(1, 8, 'ventralberry@gmail.com', '09562672240');

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

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `buyer_id`, `status_id`, `order_date`) VALUES
(1, 1, 8, '2025-05-13'),
(2, 1, 5, '2025-05-13'),
(3, 1, 2, '2025-05-13'),
(4, 1, 2, '2025-05-13');

-- --------------------------------------------------------

--
-- Table structure for table `order_details`
--

CREATE TABLE `order_details` (
  `order_detail_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `color_name` varchar(50) DEFAULT NULL,
  `customization_id` int(11) DEFAULT NULL,
  `order_quantity` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_details`
--

INSERT INTO `order_details` (`order_detail_id`, `order_id`, `product_id`, `color_name`, `customization_id`, `order_quantity`) VALUES
(4, 2, 2, 'Zombie Hamburglar', NULL, 1),
(5, 2, 2, 'Birdie Wings', NULL, 1),
(6, 2, 2, 'Soda Potion', NULL, 1),
(7, 3, 2, 'Zombie Hamburglar', NULL, 5),
(8, 3, 2, 'Birdie Wings', NULL, 5),
(9, 3, 2, 'Soda Potion', NULL, 10),
(10, 4, 2, 'Zombie Hamburglar', NULL, 103);

-- --------------------------------------------------------

--
-- Table structure for table `order_status`
--

CREATE TABLE `order_status` (
  `status_id` int(11) NOT NULL,
  `status_name` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_status`
--

INSERT INTO `order_status` (`status_id`, `status_name`) VALUES
(1, 'Order Cancelled'),
(2, 'Processing'),
(3, 'Pending'),
(4, 'Shipped'),
(5, 'Delivered'),
(6, 'Cancelled'),
(7, 'Hotdog hotdog'),
(8, 'Hotdog hotdogsdf');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `product_name` varchar(100) DEFAULT NULL,
  `available_quantity` int(11) DEFAULT NULL,
  `base_price` decimal(19,4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `category_id`, `product_name`, `available_quantity`, `base_price`) VALUES
(2, 1, 'Mcdo Minecraft Toys', 100, 500.0000);

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
(1, 'Toy');

-- --------------------------------------------------------

--
-- Table structure for table `product_colors`
--

CREATE TABLE `product_colors` (
  `color_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `color_name` varchar(50) NOT NULL,
  `image_url` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_colors`
--

INSERT INTO `product_colors` (`color_id`, `product_id`, `color_name`, `image_url`) VALUES
(19, 2, 'Zombie Hamburglar', 'https://venb.ddns.net/gallery/IMG_20250512_010923_069.jpg'),
(20, 2, 'Birdie Wings', 'https://venb.ddns.net/gallery/received_708802188248720.jpeg'),
(21, 2, 'Soda Potion', 'https://venb.ddns.net/gallery/received_1100308605190148.jpeg');

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
(5, NULL, NULL, 'admin', '$2y$10$gCiV6UrePI5nqBhPOEqGGOUqzcvYowJ.K06ENsS3IkboVlrMv8NqG', 'admin'),
(7, NULL, NULL, 'seller1', '$2y$10$1ZIoLMFC1LbwlYJeTiT.7.U8.xp2HYF33ZOF3HDZMmVizjG5MVAZy', 'seller'),
(8, 'Karl Zyrele', 'Palomo', 'buyer', '$2y$10$20gHDBA3wXSuG.FqYplnYurO39wM7xzdTkKhl2tcnJkI0/4SOoMVW', 'buyer');

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
-- Indexes for table `product_colors`
--
ALTER TABLE `product_colors`
  ADD PRIMARY KEY (`color_id`),
  ADD KEY `product_id` (`product_id`);

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
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `buyers`
--
ALTER TABLE `buyers`
  MODIFY `buyer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `order_details`
--
ALTER TABLE `order_details`
  MODIFY `order_detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `order_status`
--
ALTER TABLE `order_status`
  MODIFY `status_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `product_categories`
--
ALTER TABLE `product_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `product_colors`
--
ALTER TABLE `product_colors`
  MODIFY `color_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `seller`
--
ALTER TABLE `seller`
  MODIFY `seller_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
-- Constraints for table `product_colors`
--
ALTER TABLE `product_colors`
  ADD CONSTRAINT `product_colors_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `seller`
--
ALTER TABLE `seller`
  ADD CONSTRAINT `seller_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
