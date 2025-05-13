CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(100) NOT NULL,
    role ENUM('buyer', 'seller', 'admin') NOT NULL
);

CREATE TABLE buyers (
    buyer_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    email VARCHAR(50) UNIQUE NOT NULL,
    phone_number VARCHAR(17) UNIQUE NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users (user_id) ON DELETE CASCADE
);

CREATE TABLE addresses (
    address_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    country VARCHAR(50) NOT NULL,
    city VARCHAR(50) NOT NULL,
    barangay VARCHAR(50) NOT NULL,
    house_number VARCHAR(20) NOT NULL,
    postal_code VARCHAR(10) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users (user_id) ON DELETE CASCADE
);

CREATE TABLE order_status (
    status_id INT PRIMARY KEY AUTO_INCREMENT,
    status_name VARCHAR(50) NOT NULL
);

CREATE TABLE orders (
    order_id INT PRIMARY KEY AUTO_INCREMENT,
    buyer_id INT NOT NULL,
    status_id INT NOT NULL,
    order_date DATE NOT NULL,
    FOREIGN KEY (buyer_id) REFERENCES buyers (buyer_id) ON DELETE CASCADE,
    FOREIGN KEY (status_id) REFERENCES order_status (status_id) ON DELETE CASCADE
);

CREATE TABLE product_categories (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    category_name VARCHAR(50) NOT NULL
);

CREATE TABLE products (
    product_id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT NOT NULL,
    product_name VARCHAR(100) NOT NULL,
    available_quantity INT NOT NULL DEFAULT 0,
    base_price DECIMAL(19,4) NOT NULL,
    FOREIGN KEY (category_id) REFERENCES product_categories (category_id) ON DELETE CASCADE
);

CREATE TABLE product_variants (
  variant_id INT PRIMARY KEY AUTO_INCREMENT,
  product_id INT NOT NULL,
  variant_name VARCHAR(50) NOT NULL,
  image_url VARCHAR(255) NOT NULL,
  FOREIGN KEY (product_id) REFERENCES products (product_id) ON DELETE CASCADE
)

CREATE TABLE customizations (
    customization_id INT PRIMARY KEY AUTO_INCREMENT,
    buyer_id INT NOT NULL,
    customized_name VARCHAR(20),
    customized_name_color VARCHAR(50),
    customization_cost DECIMAL(19,4) NOT NULL,
    FOREIGN KEY (buyer_id) REFERENCES buyers (buyer_id) ON DELETE CASCADE
);

CREATE TABLE charms (
    charm_id INT PRIMARY KEY AUTO_INCREMENT,
    charm_name VARCHAR(30) NOT NULL,
    charm_base_price DECIMAL(19,4) NOT NULL
);

CREATE TABLE customization_charms (
    customization_charm_id INT PRIMARY KEY AUTO_INCREMENT,
    customization_id INT NOT NULL,
    charm_id INT NOT NULL,
    x_position INT NOT NULL,
    y_position INT NOT NULL,
    FOREIGN KEY (customization_id) REFERENCES customizations (customization_id) ON DELETE CASCADE,
    FOREIGN KEY (charm_id) REFERENCES charms (charm_id) ON DELETE CASCADE
);

CREATE TABLE order_details (
    order_detail_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    customization_id INT NOT NULL,
    charm_name VARCHAR(30) NOT NULL,
    variant_name VARCHAR(50) NOT NULL,
    variant_url VARCHAR(255) NOT NULL,
    order_quantity INT NOT NULL,
    base_price_at_order DECIMAL(19,4) NOT NULL,
    total_price_at_order DECIMAL(19,4) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders (order_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products (product_id) ON DELETE CASCADE,
    FOREIGN KEY (customization_id) REFERENCES customizations (customization_id) ON DELETE CASCADE
);

-- TABLES THAT HAVE CHANGED PRODUCTS, PRODUCT_VARIANTS, ORDER_DETAILS