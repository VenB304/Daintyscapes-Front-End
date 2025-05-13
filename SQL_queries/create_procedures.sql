DELIMITER $$
CREATE PROCEDURE add_buyer(
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

-- ----------------------------------------------------------

CREATE PROCEDURE add_product_category(
	IN p_product_category_name VARCHAR(50)
)
BEGIN 
	START TRANSACTION;
		INSERT INTO product_categories (category_name)
		VALUES (p_product_category_name);
    COMMIT;
END $$

-- ----------------------------------------------------------

CREATE PROCEDURE add_product(
    IN p_product_category_name TEXT,
    IN p_product_name VARCHAR(100),
    IN p_available_quantity INT,
    IN p_base_price DECIMAL(19,4)
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

        INSERT INTO products (category_id, product_name, available_quantity, base_price)
        VALUES (fk_category_id, p_product_name, p_available_quantity, p_base_price);

        SELECT LAST_INSERT_ID() AS product_id;
    COMMIT;
END$$

-- ----------------------------------------------------------

CREATE PROCEDURE add_product_variant(
    IN p_product_id INT,
    IN p_variant_name VARCHAR(50),
    IN p_image_url VARCHAR(255)
)
BEGIN
    INSERT INTO product_variants (product_id, variant_name, image_url)
    VALUES (p_product_id, p_variant_name, p_image_url);
END$$

-- ----------------------------------------------------------

CREATE PROCEDURE add_order(
    IN p_buyer_id INT,
    IN p_status_id INT,
    IN p_product_id INT,
    IN p_customization_id INT,
    IN p_charm_id INT,
    IN p_charm_name VARCHAR(30),
    IN p_variant_name VARCHAR(50),
    IN p_variant_url VARCHAR(255),
    IN p_order_quantity INT,
    IN p_base_price DECIMAL(19,4),
    IN p_total_price DECIMAL(19,4)
)
BEGIN
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
END $$

-- ----------------------------------------------------------

CREATE PROCEDURE add_charm(
	IN p_charm_name VARCHAR(30),
    IN p_charm_base_price DECIMAL(19,4)
)
BEGIN
	START TRANSACTION;
		INSERT INTO charms (charm_name, charm_base_price)
        VALUES (p_charm_name, p_charm_base_price);
    COMMIT;
END $$

-- ----------------------------------------------------------

CREATE PROCEDURE add_order_detail(
    IN p_order_id INT,
    IN p_product_id INT,
    IN p_color_name VARCHAR(50),
    IN p_order_quantity INT,
    IN p_base_price DECIMAL(19,4),
    IN p_total_price DECIMAL(19,4),
    IN p_charm_name VARCHAR(30),
    IN p_variant_url VARCHAR(255)
)
BEGIN
    INSERT INTO order_details (
        order_id, product_id, variant_name, order_quantity, base_price_at_order, total_price_at_order, charm_name, variant_url
    ) VALUES (
        p_order_id, p_product_id, p_color_name, p_order_quantity, p_base_price, p_total_price, p_charm_name, p_variant_url
    );
END$$
DELIMITER ;

-- ----------------------------------------------------------

CREATE PROCEDURE create_order(
    IN p_buyer_id INT,
    IN p_status_id INT
)
BEGIN
    INSERT INTO orders (buyer_id, status_id, order_date)
    VALUES (p_buyer_id, p_status_id, NOW());
    SELECT LAST_INSERT_ID() AS order_id;
END$$

-- ----------------------------------------------------------

CREATE PROCEDURE update_buyer(
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

-- ----------------------------------------------------------

CREATE PROCEDURE modify_product(
    IN p_product_id INT,
    IN p_product_category_name TEXT,
    IN p_product_name VARCHAR(100),
    IN p_available_quantity INT,
    IN p_base_price DECIMAL(19,4)
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
            available_quantity = p_available_quantity,
            base_price = p_base_price
        WHERE product_id = p_product_id;
    COMMIT;
END$$

-- ----------------------------------------------------------
-- ----------------------------------------------------------
-- ----------------------------------------------------------

CREATE PROCEDURE get_products(
    IN p_search VARCHAR(100),
    IN p_min_price DECIMAL(19,4),
    IN p_max_price DECIMAL(19,4),
    IN p_sort VARCHAR(20),
    IN p_category_id INT
)
BEGIN
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

-- ----------------------------------------------------------

CREATE PROCEDURE get_product_by_id(
	IN p_product_id INT
)
BEGIN
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

-- ----------------------------------------------------------

CREATE PROCEDURE get_buyer_profile_by_username(
	IN p_username VARCHAR(50)
)
BEGIN
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

-- ----------------------------------------------------------

CREATE PROCEDURE get_buyer_orders(
    IN p_username VARCHAR(50)
)
BEGIN
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
    WHERE o.buyer_id = (SELECT buyer_id FROM buyers WHERE username = p_username)
    ORDER BY o.order_date DESC, o.order_id DESC;
END$$

-- ----------------------------------------------------------

CREATE PROCEDURE get_seller_products()
BEGIN
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

-- ----------------------------------------------------------
-- ----------------------------------------------------------
-- ----------------------------------------------------------

CREATE PROCEDURE debug_show_buyer_details()
BEGIN
	SELECT users.user_id, role, first_name, last_name, username, password_hash, email, phone_number, country, city, barangay, house_number, postal_code
    FROM users INNER JOIN buyers ON users.user_id = buyers.user_id INNER JOIN addresses ON buyers.user_id = addresses.user_id;
END $$

-- -----------------------------------------------------------

CREATE PROCEDURE debug_change_buyer_into_admin(
    IN p_buyer_id INT,
    IN p_new_username VARCHAR(50)
)
BEGIN
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
END $$

-- ----------------------------------------------------------

CREATE PROCEDURE debug_change_buyer_into_seller(
    IN p_buyer_id INT,
    IN p_new_username VARCHAR(50)
)
BEGIN
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
END $$

-- ----------------------------------------------------------

CREATE PROCEDURE debug_add_seller_with_hash(
	IN p_username VARCHAR(50),
	IN p_password_hash VARCHAR(100)
)
BEGIN
	START TRANSACTION;

		INSERT INTO users (username, password_hash, role)
		VALUES (p_username, p_password_hash, 'seller');

		SET @uid = LAST_INSERT_ID();

		INSERT INTO seller (user_id)
		VALUES (@uid);

	COMMIT;
END $$

-- ----------------------------------------------------------

CREATE PROCEDURE remove_product(IN p_product_id INT)
BEGIN
    START TRANSACTION;
        DELETE FROM order_details WHERE product_id = p_product_id;
        DELETE FROM products WHERE product_id = p_product_id;
        DELETE FROM product_categories 
            WHERE category_id NOT IN (SELECT DISTINCT category_id FROM products);
    COMMIT;
END$$

-- ----------------------------------------------------------

CREATE PROCEDURE remove_product_variant(
    IN p_product_id INT
)
BEGIN
    DELETE FROM product_variants WHERE product_id = p_product_id;
END$$

-- ----------------------------------------------------------

CREATE PROCEDURE delete_buyer(
	IN p_buyer_id INT
)
BEGIN
	DECLARE v_user_id INT;

	START TRANSACTION;
		SELECT user_id INTO v_user_id FROM buyers WHERE buyer_id = p_buyer_id;
        DELETE FROM addresses WHERE addresses.user_id = v_user_id;
        DELETE FROM buyers WHERE buyers.buyer_id = p_buyer_id;
        DELETE FROM users WHERE users.user_id = v_user_id;
    COMMIT;
END $$

-- ----------------------------------------------------------

-- ----------------------------------------------------------
-- ----------------------------------------------------------
-- ----------------------------------------------------------

CREATE PROCEDURE initialize_add_order_status()
BEGIN
	START TRANSACTION;
		INSERT INTO order_status (status_id, status_name) 
		VALUES	(1, 'Pending'),
				(2, 'Shipped'),
				(3, 'Delivered'),
				(4, 'Cancelled');
	COMMIT;
END $$
	
-- ----------------------------------------------------------
-- ----------------------------------------------------------
-- ----------------------------------------------------------

DELIMITER ;
