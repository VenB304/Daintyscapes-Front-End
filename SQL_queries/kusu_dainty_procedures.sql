DELIMITER $$
CREATE PROCEDURE add_buyer(
	IN p_firstname VARCHAR(50),
    IN p_lastname VARCHAR(50),
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

	START TRANSACTION;

	INSERT INTO users (first_name, last_name, username, password_hash, role)
	VALUES (p_firstname, p_lastname, p_username, p_password_hash, 'buyer');

	-- Get the last inserted ID
	SET @buyer_uid = LAST_INSERT_ID();
    
	INSERT INTO buyers (user_id, email, phone_number)
	VALUES (@buyer_uid, p_email, p_phone_number);

	INSERT INTO addresses (buyer_id, country, city, barangay, house_number, postal_code)
    VALUES (@buyer_uid, p_country, p_city, p_barangay, p_house_number, p_postal_code);

	COMMIT;

END $$

-- ----------------------------------------------------------

CREATE PROCEDURE add_seller(
	IN p_username VARCHAR(50),
	IN p_password_hash VARCHAR(100)
)
BEGIN
	START TRANSACTION;

		INSERT INTO users (username, password_hash, role)
		VALUES (p_username, p_password_hash, 'seller');

		-- Get the last inserted ID
		SET @uid = LAST_INSERT_ID();

		INSERT INTO seller (user_id)
		VALUES (@uid);

	COMMIT;
END $$

-- ----------------------------------------------------------

CREATE PROCEDURE add_product_category(
	IN p_product_category_name TEXT
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
    IN p_product_color VARCHAR(50),
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
			ROLLBACK;
		ELSE           
			INSERT INTO products (category_id, product_name, product_color, available_quantity, base_price)
			VALUES (fk_category_id, p_product_name, p_product_color, p_available_quantity, p_base_price);
		    
            COMMIT;
		END IF;
END $$

-- ----------------------------------------------------------

CREATE PROCEDURE add_order(
	IN p_buyer_id INT,
    IN p_status_id INT,
    IN p_product_id INT,
    IN p_customization_id INT,
    IN p_order_quantity INT
)
BEGIN
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
            
            INSERT INTO order_details (order_id, product_id, customization_id, order_quantity)
            VALUES (LAST_INSERT_ID(), p_product_id, p_customization_id, p_order_quantity); 
            
            UPDATE products
            SET available_quantity = available_quantity - p_order_quantity
            WHERE product_id = p_product_id;    
		END IF;
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
-- ----------------------------------------------------------
-- ----------------------------------------------------------

CREATE PROCEDURE add_order_status()
BEGIN
	START TRANSACTION;
    INSERT INTO order_status (status_id, status_name) 
    VALUES	(1, 'Pending'),
			(2, 'Paid'),
			(3, 'Shipped'),
			(4, 'Delivered'),
			(5, 'Cancelled');
	COMMIT;
END $$
	
-- ----------------------------------------------------------
-- ----------------------------------------------------------
-- ----------------------------------------------------------

CREATE PROCEDURE update_buyer(
	IN p_username VARCHAR(50),
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
END $$

-- ----------------------------------------------------------

CREATE PROCEDURE modify_product(
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

-- ----------------------------------------------------------

CREATE PROCEDURE debug_show_buyer_details()
BEGIN
	SELECT users.user_id, first_name, last_name, username, password_hash, email, phone_number, country, city, barangay, house_number, postal_code
    FROM users INNER JOIN buyers ON users.user_id = buyers.user_id INNER JOIN addresses ON buyers.user_id = addresses.user_id;
END$$

-- ----------------------------------------------------------

-- ----------------------------------------------------------
