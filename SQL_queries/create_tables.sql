CREATE TABLE users(
	user_id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
	username VARCHAR(50) UNIQUE NOT NULL,
	password_hash VARCHAR(100) NOT NULL,
	role ENUM('buyer', 'seller', 'admin') NOT NULL
);

CREATE TABLE buyers(
	buyer_id INT PRIMARY KEY AUTO_INCREMENT,
	user_id INT,
	email VARCHAR(50) UNIQUE,
	phone_number VARCHAR(17) UNIQUE,
    FOREIGN KEY (user_id) REFERENCES users (user_id)
);

CREATE TABLE seller(
	seller_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    FOREIGN KEY (user_id) REFERENCES users (user_id)
);

CREATE TABLE addresses(
	address_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    country VARCHAR(50),
    city VARCHAR(50),
    barangay VARCHAR(50),
    house_number VARCHAR(20),
    postal_code VARCHAR(10),
    FOREIGN KEY (user_id) REFERENCES buyers (user_id)
);

CREATE TABLE order_status(
	status_id INT PRIMARY KEY AUTO_INCREMENT,
    status_name TEXT
);

CREATE TABLE orders(
	order_id INT PRIMARY KEY AUTO_INCREMENT,
    buyer_id INT,
    status_id INT,
    order_date DATE,
    FOREIGN KEY (buyer_id) REFERENCES buyers (buyer_id),
    FOREIGN KEY (status_id) REFERENCES order_status (status_id)
);

CREATE TABLE product_categories(
	category_id INT PRIMARY KEY AUTO_INCREMENT,
    category_name VARCHAR(50)
);

CREATE TABLE products(
	product_id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT,
    product_name VARCHAR(100),
    product_color VARCHAR(50),
    available_quantity INT,
    base_price DECIMAL(19,4),
    image_url VARCHAR(1024)
);

CREATE TABLE customizations(
	customization_id INT PRIMARY KEY AUTO_INCREMENT,
    buyer_id INT,
    customized_name VARCHAR(20),
    customized_name_color VARCHAR(50),
    customization_cost DECIMAL(19,4)
);

CREATE TABLE charms(
	charm_id INT PRIMARY KEY AUTO_INCREMENT,
    charm_name VARCHAR(30),
    charm_base_price DECIMAL(19,4)
);

CREATE TABLE customization_charms(
	customization_charm_id INT PRIMARY KEY AUTO_INCREMENT,
    customization_id INT,
    charm_id INT,
    x_position INT,
    y_position INT,
    FOREIGN KEY (customization_id) REFERENCES customizations (customization_id),
    FOREIGN KEY (charm_id) REFERENCES charms (charm_id)
);

CREATE TABLE order_details(
	order_detail_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT,
    product_id INT,
    customization_id INT,
    order_quantity INT,
    FOREIGN KEY (order_id) REFERENCES orders (order_id),
    FOREIGN KEY (product_id) REFERENCES products (product_id),
    FOREIGN KEY (customization_id) REFERENCES customizations (customization_id)
);


-- Add UNIQUE constraint to the foreign keys
-- IMPLEMENT ON DELETE CASCASE