-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 14, 2025 at 08:00 AM
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

    START TRANSACTION;
		INSERT INTO users (first_name, last_name, username, password_hash, role)
		VALUES (p_first_name, p_last_name, p_username, p_password_hash, 'buyer');
        
		SET v_user_id = LAST_INSERT_ID();

		INSERT INTO buyers (user_id, email, phone_number)
		VALUES (v_user_id, p_email, p_phone_number);

		INSERT INTO addresses (user_id, country, city, barangay, house_number, postal_code)
		VALUES (v_user_id, p_country, p_city, p_barangay, p_house_number, p_postal_code);
    COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `add_charm` (IN `p_charm_name` VARCHAR(30), IN `p_charm_base_price` DECIMAL(19,4), IN `p_charm_image_url` VARCHAR(255))   BEGIN
    START TRANSACTION;
        INSERT INTO charms (charm_name, charm_base_price, charm_image_url)
        VALUES (p_charm_name, p_charm_base_price, p_charm_image_url);
    COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `add_customization` (IN `p_buyer_id` INT, IN `p_customized_name` VARCHAR(255), IN `p_customized_name_color` VARCHAR(50), IN `p_customization_cost` DECIMAL(10,2), OUT `p_customization_id` INT)   BEGIN
    INSERT INTO customizations (buyer_id, customized_name, customized_name_color, customization_cost)
    VALUES (p_buyer_id, p_customized_name, p_customized_name_color, p_customization_cost);
    SET p_customization_id = LAST_INSERT_ID();
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `add_customization_charm` (IN `p_customization_id` INT, IN `p_charm_id` INT, IN `p_x_position` INT, IN `p_y_position` INT)   BEGIN
    INSERT INTO customization_charms (customization_id, charm_id, x_position, y_position)
    VALUES (p_customization_id, p_charm_id, p_x_position, p_y_position);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `add_order` (IN `p_buyer_id` INT, IN `p_status_id` INT, IN `p_product_id` INT, IN `p_customization_id` INT, IN `p_charm_id` INT, IN `p_charm_name` VARCHAR(30), IN `p_variant_name` VARCHAR(50), IN `p_variant_url` VARCHAR(255), IN `p_order_quantity` INT, IN `p_base_price` DECIMAL(19,4), IN `p_total_price` DECIMAL(19,4))   BEGIN
    DECLARE v_order_id INT;
    DECLARE product_quantity INT;

    START TRANSACTION;

        -- Check stock available_quantity
        SELECT available_quantity INTO product_quantity
        FROM products
        WHERE product_id = p_product_id;

        IF product_quantity < p_order_quantity THEN
            ROLLBACK;
            SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Available Quantity Low.';
        ELSE
            INSERT INTO orders (buyer_id, status_id, order_date)
            VALUES (p_buyer_id, p_status_id, CURDATE());

            SET v_order_id = LAST_INSERT_ID();

            INSERT INTO order_details (
                order_id, product_id, customization_id, charm_id, charm_name, variant_name, variant_url, order_quantity, base_price_at_order, total_price_at_order
            ) VALUES (
                v_order_id, p_product_id, p_customization_id, p_charm_id, p_charm_name, p_variant_name, p_variant_url, p_order_quantity, p_base_price, p_total_price
            );

            UPDATE products
            SET available_quantity = available_quantity - p_order_quantity
            WHERE product_id = p_product_id;
        END IF;

    COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `add_order_detail` (IN `p_order_id` INT, IN `p_product_id` INT, IN `p_customization_id` INT, IN `p_charm_id` INT, IN `p_charm_name` VARCHAR(30), IN `p_variant_name` VARCHAR(50), IN `p_variant_url` VARCHAR(255), IN `p_order_quantity` INT, IN `p_base_price` DECIMAL(19,4), IN `p_total_price` DECIMAL(19,4))   BEGIN
    START TRANSACTION;
        INSERT INTO order_details (
            order_id, product_id, customization_id, charm_id, charm_name, variant_name, variant_url, order_quantity, base_price_at_order, total_price_at_order
        ) VALUES (
            p_order_id, p_product_id, p_customization_id, p_charm_id, p_charm_name, p_variant_name, p_variant_url, p_order_quantity, p_base_price, p_total_price
        );

        UPDATE products
        SET available_quantity = available_quantity - p_order_quantity
        WHERE product_id = p_product_id;
    COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `add_order_status` (IN `p_status_name` VARCHAR(255), OUT `p_status_id` INT)   BEGIN
    INSERT INTO order_status (status_name) VALUES (p_status_name);
    SET p_status_id = LAST_INSERT_ID();
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

        SELECT LAST_INSERT_ID() AS product_id;
    COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `add_product_category` (IN `p_product_category_name` VARCHAR(50))   BEGIN 
	START TRANSACTION;
		INSERT INTO product_categories (category_name)
		VALUES (p_product_category_name);
    COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `add_product_variant` (IN `p_product_id` INT, IN `p_variant_name` VARCHAR(50), IN `p_image_url` VARCHAR(255))   BEGIN
    INSERT INTO product_variants (product_id, variant_name, image_url)
    VALUES (p_product_id, p_variant_name, p_image_url);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `create_order` (IN `p_buyer_id` INT, IN `p_status_id` INT)   BEGIN
    INSERT INTO orders (buyer_id, status_id, order_date)
    VALUES (p_buyer_id, p_status_id, NOW());
    SELECT LAST_INSERT_ID() AS order_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `debug_add_seller_with_hash` (IN `p_username` VARCHAR(50), IN `p_password_hash` VARCHAR(100))   BEGIN
	START TRANSACTION;

		INSERT INTO users (username, password_hash, role)
		VALUES (p_username, p_password_hash, 'seller');

		SET @uid = LAST_INSERT_ID();

		INSERT INTO seller (user_id)
		VALUES (@uid);

	COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `debug_change_buyer_into_admin` (IN `p_buyer_id` INT, IN `p_new_username` VARCHAR(50))   BEGIN
    DECLARE v_username VARCHAR(50);
    DECLARE v_password_hash VARCHAR(100);
    DECLARE v_user_id INT;

    START TRANSACTION;
        -- Get user_id, username, and password_hash for the buyer
        SELECT users.user_id, users.username, users.password_hash
        INTO v_user_id, v_username, v_password_hash
        FROM users
        JOIN buyers ON users.user_id = buyers.user_id
        WHERE buyers.buyer_id = p_buyer_id
        LIMIT 1;

        IF v_username IS NULL OR v_password_hash IS NULL THEN
            ROLLBACK;
        ELSE
            UPDATE users
            SET username = p_new_username,
                role = 'admin'
            WHERE user_id = v_user_id;

            DELETE FROM buyers WHERE buyer_id = p_buyer_id;
            DELETE FROM addresses WHERE user_id = v_user_id;
        END IF;

    COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `debug_change_buyer_into_seller` (IN `p_buyer_id` INT, IN `p_new_username` VARCHAR(50))   BEGIN
    DECLARE v_username VARCHAR(50);
    DECLARE v_password_hash VARCHAR(100);
    DECLARE v_user_id INT;

    START TRANSACTION;
        -- Get user_id, username, and password_hash for the buyer
        SELECT users.user_id, users.username, users.password_hash
        INTO v_user_id, v_username, v_password_hash
        FROM users
        JOIN buyers ON users.user_id = buyers.user_id
        WHERE buyers.buyer_id = p_buyer_id
        LIMIT 1;

        IF v_username IS NULL OR v_password_hash IS NULL THEN
            ROLLBACK;
        ELSE
            UPDATE users
            SET username = p_new_username,
                role = 'seller'
            WHERE user_id = v_user_id;

            DELETE FROM buyers WHERE buyer_id = p_buyer_id;
            DELETE FROM addresses WHERE user_id = v_user_id;
        END IF;

    COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `debug_show_buyer_details` ()   BEGIN
	SELECT users.user_id, role, first_name, last_name, username, password_hash, email, phone_number, country, city, barangay, house_number, postal_code
    FROM users INNER JOIN buyers ON users.user_id = buyers.user_id INNER JOIN addresses ON buyers.user_id = addresses.user_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `delete_buyer` (IN `p_buyer_id` INT)   BEGIN
	DECLARE v_user_id INT;

	START TRANSACTION;
		SELECT user_id INTO v_user_id FROM buyers WHERE buyer_id = p_buyer_id;
        DELETE FROM addresses WHERE addresses.user_id = v_user_id;
        DELETE FROM buyers WHERE buyers.buyer_id = p_buyer_id;
        DELETE FROM users WHERE users.user_id = v_user_id;
    COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `get_buyer_orders` (IN `p_username` VARCHAR(50))   BEGIN
    SELECT 
        o.order_id,
        o.order_date AS date,
        s.status_name AS status,
        od.product_id,
        p.product_name AS name,
        od.variant_name,
        od.variant_url AS image,
        od.order_quantity AS quantity,
        od.base_price_at_order,
        od.total_price_at_order,
        c.charm_name,
        cc.x_position,
        cc.y_position,
        cust.customized_name AS engraving_name,
        cust.customized_name_color AS engraving_color
        FROM orders o
        JOIN order_status s ON o.status_id = s.status_id
        JOIN order_details od ON o.order_id = od.order_id
        JOIN products p ON od.product_id = p.product_id
        LEFT JOIN customizations cust ON od.customization_id = cust.customization_id
        LEFT JOIN customization_charms cc ON cust.customization_id = cc.customization_id
        LEFT JOIN charms c ON cc.charm_id = c.charm_id
        WHERE o.buyer_id = (SELECT buyer_id FROM buyers b WHERE b.user_id = (SELECT user_id FROM users u WHERE u.username = p_username))
        ORDER BY o.order_date DESC, o.order_id DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `get_buyer_profile_by_username` (IN `p_username` VARCHAR(50))   BEGIN
    SELECT 
        users.username, 
        users.first_name,
        users.last_name,
        buyers.email, 
        buyers.phone_number, 
        addresses.country, 
        addresses.city, 
        addresses.postal_code, 
        addresses.barangay, 
        addresses.house_number
    FROM users
    JOIN buyers ON users.user_id = buyers.user_id
    LEFT JOIN addresses ON buyers.user_id = addresses.user_id
    WHERE users.username = p_username
    LIMIT 1;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `get_products` (IN `p_search` VARCHAR(100), IN `p_min_price` DECIMAL(19,4), IN `p_max_price` DECIMAL(19,4), IN `p_sort` VARCHAR(20), IN `p_category_id` INT)   BEGIN
    SELECT 
        p.product_id AS id,
        p.product_name AS name,
        p.base_price,
        p.available_quantity,
        (SELECT image_url FROM product_variants 
        WHERE product_id = p.product_id 
        ORDER BY variant_id ASC LIMIT 1) AS image
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

CREATE DEFINER=`root`@`localhost` PROCEDURE `get_seller_products` ()   BEGIN
    SELECT 
        p.product_id, 
        p.product_name, 
        c.category_name,
        p.available_quantity, 
        p.base_price,
        GROUP_CONCAT(pv.variant_name SEPARATOR ', ') AS colors
    FROM products p
    LEFT JOIN product_categories c ON p.category_id = c.category_id
    LEFT JOIN product_variants pv ON p.product_id = pv.product_id
    GROUP BY p.product_id
    ORDER BY p.product_id DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `initialize_add_order_status` ()   BEGIN
	START TRANSACTION;
		INSERT INTO order_status (status_id, status_name) 
		VALUES	(1, 'Pending'),
				(2, 'Shipped'),
				(3, 'Delivered'),
				(4, 'Cancelled');
	COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `modify_charm` (IN `p_charm_id` INT, IN `p_charm_name` VARCHAR(30), IN `p_charm_base_price` DECIMAL(19,4), IN `p_charm_image_url` VARCHAR(255))   BEGIN
    UPDATE charms
    SET charm_name = p_charm_name,
        charm_base_price = p_charm_base_price,
        charm_image_url = p_charm_image_url
    WHERE charm_id = p_charm_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `modify_product` (IN `p_product_id` INT, IN `p_product_category_name` TEXT, IN `p_product_name` VARCHAR(100), IN `p_available_quantity` INT, IN `p_base_price` DECIMAL(19,4))   BEGIN
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

CREATE DEFINER=`root`@`localhost` PROCEDURE `remove_charm` (IN `p_charm_id` INT)   BEGIN
    DELETE FROM charms WHERE charm_id = p_charm_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `remove_product` (IN `p_product_id` INT)   BEGIN
    START TRANSACTION;
        DELETE FROM order_details WHERE product_id = p_product_id;
        DELETE FROM products WHERE product_id = p_product_id;
        DELETE FROM product_categories 
            WHERE category_id NOT IN (SELECT DISTINCT category_id FROM products);
    COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `remove_product_variant` (IN `p_product_id` INT)   BEGIN
    DELETE FROM product_variants WHERE product_id = p_product_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `update_buyer` (IN `p_username` VARCHAR(50), IN `p_first_name` VARCHAR(50), IN `p_last_name` VARCHAR(50), IN `p_email` VARCHAR(50), IN `p_phone_number` VARCHAR(17), IN `p_country` VARCHAR(50), IN `p_city` VARCHAR(50), IN `p_barangay` VARCHAR(50), IN `p_house_number` VARCHAR(20), IN `p_postal_code` VARCHAR(10))   BEGIN
    DECLARE v_user_id INT;
    
    START TRANSACTION;
		SELECT user_id INTO v_user_id FROM users WHERE username = p_username;

		UPDATE users
		SET first_name = p_first_name, last_name = p_last_name
		WHERE user_id = v_user_id;

		UPDATE buyers
		SET email = p_email, phone_number = p_phone_number
		WHERE user_id = v_user_id;

		UPDATE addresses
		SET country = p_country,
			city = p_city,
			barangay = p_barangay,
			house_number = p_house_number,
			postal_code = p_postal_code
		WHERE user_id = v_user_id;
    COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `update_buyer_credentials` (IN `p_username` VARCHAR(50), IN `p_password_hash` VARCHAR(255), IN `p_buyer_id` INT)   BEGIN
    UPDATE users
    INNER JOIN buyers ON users.user_id = buyers.user_id
    SET users.username = p_username,
        users.password_hash = p_password_hash
    WHERE buyers.buyer_id = p_buyer_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `update_order_status` (IN `p_order_id` INT, IN `p_status_id` INT)   BEGIN
    UPDATE orders SET status_id = p_status_id WHERE order_id = p_order_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `update_user_credentials` (IN `p_username` VARCHAR(50), IN `p_password_hash` VARCHAR(255), IN `p_user_id` INT)   BEGIN
    UPDATE users
    SET username = p_username,
        password_hash = p_password_hash
    WHERE user_id = p_user_id;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

CREATE TABLE `addresses` (
  `address_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `country` varchar(50) NOT NULL,
  `city` varchar(50) NOT NULL,
  `barangay` varchar(50) NOT NULL,
  `house_number` varchar(20) NOT NULL,
  `postal_code` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `addresses`
--

INSERT INTO `addresses` (`address_id`, `user_id`, `country`, `city`, `barangay`, `house_number`, `postal_code`) VALUES
(3, 3, 'Philippines', 'Santa Clara', 'Totod', '23', '3746');

-- --------------------------------------------------------

--
-- Table structure for table `buyers`
--

CREATE TABLE `buyers` (
  `buyer_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `email` varchar(50) NOT NULL,
  `phone_number` varchar(17) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `buyers`
--

INSERT INTO `buyers` (`buyer_id`, `user_id`, `email`, `phone_number`) VALUES
(3, 3, 'kusagaki@gmail.com', '09386254685');

-- --------------------------------------------------------

--
-- Table structure for table `charms`
--

CREATE TABLE `charms` (
  `charm_id` int(11) NOT NULL,
  `charm_name` varchar(30) NOT NULL,
  `charm_base_price` decimal(19,4) NOT NULL,
  `charm_image_url` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `charms`
--

INSERT INTO `charms` (`charm_id`, `charm_name`, `charm_base_price`, `charm_image_url`) VALUES
(1, 'Car', 25.0000, 'https://venb.ddns.net/gallery/daintyscapes/charms/car.png'),
(2, 'Small Jet', 25.0000, 'https://venb.ddns.net/gallery/daintyscapes/charms/small_jet.png'),
(3, 'Mustache', 25.0000, 'https://venb.ddns.net/gallery/daintyscapes/charms/mustache.png'),
(4, 'Steth', 25.0000, 'https://venb.ddns.net/gallery/daintyscapes/charms/steth.png'),
(5, 'Elephant', 25.0000, 'https://venb.ddns.net/gallery/daintyscapes/charms/elephant.png'),
(7, 'Planet', 25.0000, 'https://venb.ddns.net/gallery/daintyscapes/charms/planet.png');

-- --------------------------------------------------------

--
-- Table structure for table `customizations`
--

CREATE TABLE `customizations` (
  `customization_id` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `customized_name` varchar(9) DEFAULT NULL,
  `customized_name_color` varchar(50) DEFAULT NULL,
  `customization_cost` decimal(19,4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customizations`
--

INSERT INTO `customizations` (`customization_id`, `buyer_id`, `customized_name`, `customized_name_color`, `customization_cost`) VALUES
(1, 3, '', '#e9d7b9', 0.0000),
(2, 3, 'asdf', '#7b4a1e', 0.0000),
(3, 3, '', '#e9d7b9', 0.0000),
(4, 3, '', '#e9d7b9', 0.0000),
(5, 3, 'test', '#7b4a1e', 0.0000),
(6, 3, '', '#e9d7b9', 0.0000),
(7, 3, 'wrfew', '#7b4a1e', 0.0000),
(8, 3, '', '#e9d7b9', 0.0000),
(9, 3, 'rffrg', '#e9d7b9', 0.0000);

-- --------------------------------------------------------

--
-- Table structure for table `customization_charms`
--

CREATE TABLE `customization_charms` (
  `customization_charm_id` int(11) NOT NULL,
  `customization_id` int(11) NOT NULL,
  `charm_id` int(11) NOT NULL,
  `x_position` int(11) NOT NULL,
  `y_position` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customization_charms`
--

INSERT INTO `customization_charms` (`customization_charm_id`, `customization_id`, `charm_id`, `x_position`, `y_position`) VALUES
(1, 1, 1, 403, 348),
(2, 2, 4, 406, 417),
(3, 4, 1, 0, 0),
(4, 5, 2, 273, 385),
(5, 6, 3, 0, 0),
(6, 7, 5, 0, 0),
(7, 8, 3, 262, 319),
(8, 9, 5, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `status_id` int(11) NOT NULL,
  `order_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `buyer_id`, `status_id`, `order_date`) VALUES
(1, 3, 8, '2025-05-14'),
(2, 3, 7, '2025-05-14'),
(3, 3, 3, '2025-05-14'),
(4, 3, 2, '2025-05-14'),
(5, 3, 5, '2025-05-14');

-- --------------------------------------------------------

--
-- Table structure for table `order_details`
--

CREATE TABLE `order_details` (
  `order_detail_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `customization_id` int(11) DEFAULT NULL,
  `charm_id` int(11) DEFAULT NULL,
  `charm_name` varchar(30) DEFAULT NULL,
  `variant_name` varchar(50) NOT NULL,
  `variant_url` varchar(255) NOT NULL,
  `order_quantity` int(11) NOT NULL,
  `base_price_at_order` decimal(19,4) NOT NULL,
  `total_price_at_order` decimal(19,4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_details`
--

INSERT INTO `order_details` (`order_detail_id`, `order_id`, `product_id`, `customization_id`, `charm_id`, `charm_name`, `variant_name`, `variant_url`, `order_quantity`, `base_price_at_order`, `total_price_at_order`) VALUES
(11, 5, 3, 8, 3, 'Mustache', 'Zombie Hamburglar', '/daintyscapes/assets/img/Zombie%20Hamburglar.jpg', 1, 300.0000, 325.0000),
(12, 5, 2, 9, 5, 'Elephant', 'Red', '/daintyscapes/assets/img/Multi-use%20Wristlet%20Red.png', 1, 150.0000, 175.0000);

-- --------------------------------------------------------

--
-- Table structure for table `order_status`
--

CREATE TABLE `order_status` (
  `status_id` int(11) NOT NULL,
  `status_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_status`
--

INSERT INTO `order_status` (`status_id`, `status_name`) VALUES
(1, 'Pending'),
(2, 'Shipped'),
(3, 'Delivered'),
(4, 'Cancelled'),
(5, 'Processing'),
(6, 'Cancelled'),
(7, 'Cancelled'),
(8, 'putang ina mo jepoy dizon');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `available_quantity` int(11) NOT NULL DEFAULT 0,
  `base_price` decimal(19,4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `category_id`, `product_name`, `available_quantity`, `base_price`) VALUES
(2, 2, 'Multi-use Wrislet', 999, 150.0000),
(3, 3, 'Mcdonald\'s Minecraft Toys', 9999, 300.0000);

-- --------------------------------------------------------

--
-- Table structure for table `product_categories`
--

CREATE TABLE `product_categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_categories`
--

INSERT INTO `product_categories` (`category_id`, `category_name`) VALUES
(2, 'Multi-use Wrislet'),
(3, 'Toys');

-- --------------------------------------------------------

--
-- Table structure for table `product_variants`
--

CREATE TABLE `product_variants` (
  `variant_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `variant_name` varchar(50) NOT NULL,
  `image_url` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_variants`
--

INSERT INTO `product_variants` (`variant_id`, `product_id`, `variant_name`, `image_url`) VALUES
(36, 2, 'Red', '/daintyscapes/assets/img/Multi-use%20Wristlet%20Red.png'),
(37, 2, 'LIght Pastel Green', '/daintyscapes/assets/img/Multi-use%20Wristlet%20Light%20Pastel%20Green.png'),
(38, 2, 'Light Gray', '/daintyscapes/assets/img/Multi-use%20Wristlet%20Light%20Gray.png'),
(39, 2, 'Gold', '/daintyscapes/assets/img/Multi-use%20Wristlet%20Gold.png'),
(49, 3, 'Zombie Hamburglar', '/daintyscapes/assets/img/Zombie%20Hamburglar.jpg'),
(50, 3, 'Birdie Wings', '/daintyscapes/assets/img/Birdie%20WIngs.jpeg'),
(51, 3, 'Soda Potion', '/daintyscapes/assets/img/Soda%20Potion.jpeg');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(100) NOT NULL,
  `role` enum('buyer','seller','admin') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `first_name`, `last_name`, `username`, `password_hash`, `role`) VALUES
(1, 'Kusu', 'gaki', 'admin', '$2y$10$ysnGZt/sCBerBI2P6KX1dOWMevfPPZ8iAuZQpf9uPmm2SWTwjI5Ue', 'admin'),
(2, 'Kusu', 'gaki', 'seller', '$2y$10$ysnGZt/sCBerBI2P6KX1dOWMevfPPZ8iAuZQpf9uPmm2SWTwjI5Ue', 'seller'),
(3, 'Kusu', 'gaki', 'buyer', '$2y$10$ysnGZt/sCBerBI2P6KX1dOWMevfPPZ8iAuZQpf9uPmm2SWTwjI5Ue', 'buyer');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`address_id`),
  ADD KEY `user_id` (`user_id`);

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
  ADD PRIMARY KEY (`customization_id`),
  ADD KEY `buyer_id` (`buyer_id`);

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
  ADD KEY `customization_id` (`customization_id`),
  ADD KEY `charm_id` (`charm_id`);

--
-- Indexes for table `order_status`
--
ALTER TABLE `order_status`
  ADD PRIMARY KEY (`status_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `product_categories`
--
ALTER TABLE `product_categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`variant_id`),
  ADD KEY `product_id` (`product_id`);

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
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `buyers`
--
ALTER TABLE `buyers`
  MODIFY `buyer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `charms`
--
ALTER TABLE `charms`
  MODIFY `charm_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `customizations`
--
ALTER TABLE `customizations`
  MODIFY `customization_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `customization_charms`
--
ALTER TABLE `customization_charms`
  MODIFY `customization_charm_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `order_details`
--
ALTER TABLE `order_details`
  MODIFY `order_detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `order_status`
--
ALTER TABLE `order_status`
  MODIFY `status_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `product_categories`
--
ALTER TABLE `product_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `variant_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `buyers`
--
ALTER TABLE `buyers`
  ADD CONSTRAINT `buyers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `customizations`
--
ALTER TABLE `customizations`
  ADD CONSTRAINT `customizations_ibfk_1` FOREIGN KEY (`buyer_id`) REFERENCES `buyers` (`buyer_id`) ON DELETE CASCADE;

--
-- Constraints for table `customization_charms`
--
ALTER TABLE `customization_charms`
  ADD CONSTRAINT `customization_charms_ibfk_1` FOREIGN KEY (`customization_id`) REFERENCES `customizations` (`customization_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `customization_charms_ibfk_2` FOREIGN KEY (`charm_id`) REFERENCES `charms` (`charm_id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`buyer_id`) REFERENCES `buyers` (`buyer_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`status_id`) REFERENCES `order_status` (`status_id`) ON DELETE CASCADE;

--
-- Constraints for table `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_details_ibfk_3` FOREIGN KEY (`customization_id`) REFERENCES `customizations` (`customization_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `order_details_ibfk_4` FOREIGN KEY (`charm_id`) REFERENCES `charms` (`charm_id`) ON DELETE SET NULL;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `product_categories` (`category_id`) ON DELETE CASCADE;

--
-- Constraints for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `product_variants_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
