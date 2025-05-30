<?php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root'); // Default XAMPP username
define('DB_PASSWORD', '');     // Default XAMPP password
define('DB_NAME', 'lefo_db'); // Your database name

// Create connection to MySQL server
$conn_server = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD);

// Check connection
if ($conn_server->connect_error) {
    die("Connection to server failed: " . $conn_server->connect_error);
}

// Create database if it doesn't exist
$sql_create_db = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
if (!$conn_server->query($sql_create_db)) {
    die("Error creating database: " . $conn_server->error);
}
$conn_server->close();

// Create connection to the specific database
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    die("Connection to database " . DB_NAME . " failed: " . $conn->connect_error);
}

// SQL to create users table if it doesn't exist
$table_sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL, -- For hashed passwords
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (!$conn->query($table_sql)) {
    die("Error creating table users: " . $conn->error);
}

// SQL to create orders table if it doesn't exist
$orders_sql = "CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    customer_name VARCHAR(100) NOT NULL,
    customer_email VARCHAR(100) NOT NULL,
    customer_phone VARCHAR(20) NOT NULL,
    shipping_address_line1 VARCHAR(255) NOT NULL,
    shipping_address_line2 VARCHAR(255),
    shipping_city VARCHAR(100) NOT NULL,
    shipping_state VARCHAR(100),
    shipping_zip_code VARCHAR(20) NOT NULL,
    shipping_country VARCHAR(100) NOT NULL,
    order_total DECIMAL(10,2) NOT NULL,
    order_status VARCHAR(50) NOT NULL DEFAULT 'pending',
    payment_method VARCHAR(50) NOT NULL,
    payment_status VARCHAR(50) NOT NULL DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
)";

if (!$conn->query($orders_sql)) {
    die("Error creating table orders: " . $conn->error);
}

// SQL to create order_items table if it doesn't exist
$order_items_sql = "CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    item_name VARCHAR(255) NOT NULL,
    quantity INT NOT NULL,
    price_at_purchase DECIMAL(10,2) NOT NULL,
    attributes JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id)
)";

if (!$conn->query($order_items_sql)) {
    die("Error creating table order_items: " . $conn->error);
}

// SQL to create design_submissions table if it doesn't exist
$design_submissions_sql = "CREATE TABLE IF NOT EXISTS design_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    design_name VARCHAR(255) NOT NULL,
    description TEXT,
    file_path VARCHAR(255) NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    product_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
)";

if (!$conn->query($design_submissions_sql)) {
    die("Error creating table design_submissions: " . $conn->error);
}

// Add is_design column to products table if it doesn't exist
$check_design_column = $conn->query("SHOW COLUMNS FROM products LIKE 'is_design'");
if($check_design_column->num_rows == 0) {
    $alter_products = "ALTER TABLE products ADD COLUMN is_design BOOLEAN DEFAULT FALSE AFTER stock_quantity, ADD COLUMN design_id INT NULL AFTER is_design, ADD FOREIGN KEY (design_id) REFERENCES design_submissions(id) ON DELETE SET NULL";
    if (!$conn->query($alter_products)) {
        die("Error adding is_design column to products: " . $conn->error);
    }
}

// $conn will be used by other scripts that include this file.
?>