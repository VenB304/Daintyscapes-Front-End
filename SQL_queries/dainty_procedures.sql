DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `add_buyer`(
    IN p_first_name VARCHAR(50),
    IN p_last_name VARCHAR(50),
    IN p_username VARCHAR(50),
    IN p_password_hash VARCHAR(100),
    IN p_email VARCHAR(50),
    IN p_phone_number VARCHAR(17),
    IN p_country VARCHAR(50),
    IN p_city VARCHAR(50),
    IN p_barangay VARCHAR(50),
    IN p_house_number VARCHAR(20),
    IN p_postal_code VARCHAR(10)
)
BEGIN
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
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `add_product`(
    IN p_product_category_name TEXT,
    IN p_product_name VARCHAR(100),
    IN p_product_color VARCHAR(50),
    IN p_available_quantity INT,
    IN p_base_price DECIMAL(19,4),
    IN p_image_url VARCHAR(255)
)
BEGIN
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
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `add_seller`(
	IN p_username VARCHAR(50),
	IN p_password_hash VARCHAR(100)
)
BEGIN
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
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `get_buyer_orders`(IN p_username VARCHAR(50))
BEGIN
    SELECT 
        o.order_id,
        o.order_date AS date,
        os.status_name AS status,
        od.product_id,
        p.product_name AS name,
        od.order_quantity AS quantity,
        p.base_price AS price
    FROM orders o
    JOIN buyers b ON o.buyer_id = b.buyer_id
    JOIN users u ON b.user_id = u.user_id
    JOIN order_details od ON o.order_id = od.order_id
    JOIN products p ON od.product_id = p.product_id
    LEFT JOIN order_status os ON o.status_id = os.status_id
    WHERE u.username = p_username
    ORDER BY o.order_date DESC, o.order_id DESC;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `get_buyer_profile_by_username`(IN p_username VARCHAR(50))
BEGIN
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
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `get_product_by_id`(IN p_product_id INT)
BEGIN
    SELECT 
        p.product_id, 
        pc.category_name, 
        p.product_name, 
        p.product_color, 
        p.available_quantity, 
        p.base_price, 
        p.image_url
    FROM products p
    LEFT JOIN product_categories pc ON p.category_id = pc.category_id
    WHERE p.product_id = p_product_id
    LIMIT 1;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `get_products`(
    IN p_search VARCHAR(100),
    IN p_min_price DECIMAL(19,4),
    IN p_max_price DECIMAL(19,4),
    IN p_sort VARCHAR(20)
)
BEGIN
    SELECT 
        p.product_id AS id,
        p.product_name AS name,
        p.base_price,
        p.available_quantity,
        p.image_url AS image
    FROM products p
    WHERE
        (p_search IS NULL OR p.product_name LIKE CONCAT('%', p_search, '%'))
        AND (p_min_price IS NULL OR p.base_price >= p_min_price)
        AND (p_max_price IS NULL OR p.base_price <= p_max_price)
    ORDER BY
        CASE 
            WHEN p_sort = 'price_asc' THEN p.base_price
            WHEN p_sort = 'price_desc' THEN -p.base_price
            WHEN p_sort = 'oldest' THEN p.product_id
            ELSE -p.product_id
        END ASC,
        p.product_id DESC;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `modify_product`(
    IN p_product_id INT,
    IN p_product_category_name TEXT,
    IN p_product_name VARCHAR(100),
    IN p_product_color VARCHAR(50),
    IN p_available_quantity INT,
    IN p_base_price DECIMAL(19,4),
    IN p_image_url VARCHAR(255)
)
BEGIN
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
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `update_buyer`(
    IN p_username VARCHAR(50),
    IN p_first_name VARCHAR(50),
    IN p_last_name VARCHAR(50),
    IN p_email VARCHAR(50),
    IN p_phone_number VARCHAR(17),
    IN p_country VARCHAR(50),
    IN p_city VARCHAR(50),
    IN p_barangay VARCHAR(50),
    IN p_house_number VARCHAR(20),
    IN p_postal_code VARCHAR(10)
)
BEGIN
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
