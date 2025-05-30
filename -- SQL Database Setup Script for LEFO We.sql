-- SQL Database Setup Script for LEFO Website
-- Version: 1.0
-- Purpose: Sets up the initial database and tables for user authentication.

-- --------------------------------------------------------
-- Database: `lefo_db`
-- --------------------------------------------------------

-- Create the database `lefo_db` if it does not already exist.
-- Uses utf8mb4 for broad character support, including emojis.
CREATE DATABASE IF NOT EXISTS `lefo_db` 
DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Select the `lefo_db` database for subsequent operations.
USE `lefo_db`;

-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique identifier for the user',
  `username` VARCHAR(50) NOT NULL UNIQUE COMMENT 'User-chosen unique username',
  `email` VARCHAR(100) NOT NULL UNIQUE COMMENT 'User''s unique email address',
  `password` VARCHAR(255) NOT NULL COMMENT 'Hashed password for security',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Timestamp of when the user account was created',
  `is_admin` BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'Flag to indicate if user is an admin'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Stores user account information';

-- --------------------------------------------------------
-- Insert Admin User
-- --------------------------------------------------------
-- IMPORTANT: Replace 'YOUR_GENERATED_HASHED_PASSWORD_HERE' with the actual hashed password.
-- The previous INSERT statement was missing the 'is_admin' column in the column list.
INSERT INTO `users` (`username`, `email`, `password`, `is_admin`) VALUES
('l3fo', 'of3l.yin@gmail.com', '$2y$10$q/zExbGMLk.eOovnv7C3VebimGhCGlvjxoa7qCuwnx9LIR3FE96rS', TRUE); -- Set is_admin to TRUE for the admin

-- --------------------------------------------------------
-- Table structure for table `categories`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL UNIQUE,
  `description` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Product categories';

-- --------------------------------------------------------
-- Insert Sample Categories
-- --------------------------------------------------------
INSERT INTO `categories` (`name`, `description`) VALUES
('Apparel', 'Wearable items like T-Shirts and Hoodies.'),
('Accessories', 'Items like Keychains, Hats, and Phone Cases.'),
('Figurines & Models', 'Decorative or collectible 3D printed models.'),
('Home & Office', 'Functional items for home or office use like Desk Organizers.');

-- --------------------------------------------------------
-- Table structure for table `products`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique identifier for the product',
  `name` VARCHAR(255) NOT NULL COMMENT 'Product name',
  `description` TEXT NULL COMMENT 'Detailed product description',
  `price` DECIMAL(10, 2) NOT NULL COMMENT 'Product price',
  `image_path` VARCHAR(255) NOT NULL COMMENT 'Path to the product image (e.g., mockups/shirts/black_t-shirt.png)',
  `default_color` VARCHAR(50) NULL COMMENT 'Default color for display if applicable',
  `stock_quantity` INT NOT NULL DEFAULT 0 COMMENT 'Available stock for the product',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Stores product information';

-- --------------------------------------------------------
-- Insert Sample Products (Ensure image_path matches your file structure)
-- --------------------------------------------------------
INSERT INTO `products` (`name`, `description`, `price`, `image_path`, `default_color`, `stock_quantity`) VALUES
('LEFO Signature Tee', 'Comfortable and stylish tee with the LEFO logo.', 29.99, 'mockups/shirts/black t-shirt.png', 'Black', 100),
('Urban Comfort Hoodie', 'Warm and durable hoodie, perfect for everyday wear.', 59.99, 'mockups/hoodie/black hoodie.png', 'black', 50),
('Stealth Cap', 'Sleek and minimalist cap.', 24.99, 'mockups/hats/black hat.png', 'Black', 75),
('Minimalist Band Ring', 'Elegant 3D printed band ring.', 79.99, 'mockups/rings/ring 1.jpg', NULL, 30);
-- --------------------------------------------------------
-- Table structure for table `product_categories` (Pivot Table)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `product_categories` (
  `product_id` INT NOT NULL,
  `category_id` INT NOT NULL,
  PRIMARY KEY (`product_id`, `category_id`),
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Links products to categories';

-- --------------------------------------------------------
-- Link Sample Products to Categories
-- (Assuming product IDs 1-6 and category IDs 1-4 from above inserts)
-- --------------------------------------------------------
-- LEFO Signature Tee (ID 1) -> Apparel (ID 1)
INSERT INTO `product_categories` (`product_id`, `category_id`) VALUES (1, 1);
-- Urban Comfort Hoodie (ID 2) -> Apparel (ID 1)
INSERT INTO `product_categories` (`product_id`, `category_id`) VALUES (2, 1);
-- Stealth Cap (ID 3) -> Accessories (ID 2)
INSERT INTO `product_categories` (`product_id`, `category_id`) VALUES (3, 2);
-- Minimalist Band Ring (ID 4) -> Accessories (ID 2)
INSERT INTO `product_categories` (`product_id`, `category_id`) VALUES (4, 2);

-- --------------------------------------------------------
-- Table structure for table `design_submissions`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `design_submissions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique identifier for the submission',
  `user_id` INT NULL COMMENT 'Foreign key to users table if submitted by a logged-in user',
  `design_name` VARCHAR(255) NOT NULL COMMENT 'Name of the submitted design',
  `description` TEXT COMMENT 'Description of the design',
  `customization_details` TEXT NULL COMMENT 'JSON string for customization details: { tshirt_color, tshirt_size, design_position_px: {x,y}, design_dimensions_px: {width,height} }',
  `file_path` VARCHAR(255) NOT NULL COMMENT 'Path to the uploaded design file',
  `contact_email` VARCHAR(100) NOT NULL COMMENT 'Contact email for the submission',
  `contact_phone` VARCHAR(20) NULL COMMENT 'Optional contact phone number',
  `status` VARCHAR(50) NOT NULL DEFAULT 'pending_review' COMMENT 'Status: pending_review, approved, rejected, quoted',
  `submitted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Timestamp of when the design was submitted',
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Stores user-submitted design information';

-- --------------------------------------------------------
-- Table structure for table `orders`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NULL COMMENT 'FK to users table if logged in',
  `customer_name` VARCHAR(255) NOT NULL,
  `customer_email` VARCHAR(255) NOT NULL,
  `customer_phone` VARCHAR(20) NULL,
  `shipping_address_line1` VARCHAR(255) NOT NULL,
  `shipping_address_line2` VARCHAR(255) NULL,
  `shipping_city` VARCHAR(100) NOT NULL,
  `shipping_state` VARCHAR(100) NULL,
  `shipping_zip_code` VARCHAR(20) NOT NULL,
  `shipping_country` VARCHAR(100) NOT NULL DEFAULT 'Your Default Country',
  `order_total` DECIMAL(10, 2) NOT NULL,
  `order_status` VARCHAR(50) NOT NULL DEFAULT 'pending' COMMENT 'e.g., pending, processing, shipped, delivered, cancelled',
  `payment_method` VARCHAR(50) NOT NULL DEFAULT 'cash_on_delivery',
  `payment_status` VARCHAR(50) NOT NULL DEFAULT 'pending' COMMENT 'e.g., pending, paid, failed',
  `notes` TEXT NULL COMMENT 'Any special notes for the order',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Customer orders';
-- The ALTER TABLE statement below is still good as a fallback if the table was created
-- by an older version of this script.
ALTER TABLE design_submissions ADD COLUMN IF NOT EXISTS customization_details TEXT NULL AFTER file_path;

-- --------------------------------------------------------
-- Table structure for table `order_items`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL COMMENT 'FK to orders table',
  `product_id` INT NULL COMMENT 'FK to products table (for standard products)',
  `design_submission_id` INT NULL COMMENT 'FK to design_submissions table (for custom items)',
  `item_name` VARCHAR(255) NOT NULL COMMENT 'Name of the item at time of purchase',
  `item_description` TEXT NULL COMMENT 'Detailed description for this specific item in the order, summarizing customizations',
  `quantity` INT NOT NULL DEFAULT 1,
  `price_at_purchase` DECIMAL(10, 2) NOT NULL COMMENT 'Price per unit at time of purchase',
  `attributes` JSON NULL COMMENT 'Store selected attributes like size, color, custom design details as JSON',
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  FOREIGN KEY (`design_submission_id`) REFERENCES `design_submissions`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT chk_item_source CHECK (`product_id` IS NOT NULL OR `design_submission_id` IS NOT NULL) -- Ensure item is either a product or a submission
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Individual items within an order';