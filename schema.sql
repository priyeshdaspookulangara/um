-- Database schema for Siva Ganga Dance Costumes Staff User Manager
-- Version 2.0 - Refactored for new orders and customers structure

-- Table for staff user authentication and data
CREATE TABLE `staff_users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(255) UNIQUE NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(255) NOT NULL,
  `is_active` TINYINT(1) DEFAULT 1
);

-- Table for customer information
CREATE TABLE `customers` (
  `customer_id` INT AUTO_INCREMENT PRIMARY KEY,
  `customer_name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255),
  `phone` VARCHAR(50)
);

-- Table for customer orders, reflecting the new structure
CREATE TABLE `orders` (
  `order_id` INT AUTO_INCREMENT PRIMARY KEY,
  `customer_id` INT NOT NULL,
  `order_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `total_amount` INT NOT NULL,
  `is_paid` ENUM('pending', 'paid', 'partially', 'error') DEFAULT 'pending',
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`customer_id`) ON DELETE CASCADE
);

-- Table for items within an order
CREATE TABLE `order_details` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `item_id` VARCHAR(255) NOT NULL, -- e.g., a product SKU
  `quantity` INT NOT NULL,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`order_id`) ON DELETE CASCADE
);

-- Table to store tasks, refactored to remove redundant columns
CREATE TABLE `tasks` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `assigned_user_id` INT,
  `order_id` INT NULL,
  `task_type` VARCHAR(255), -- e.g., 'CONTACT_CUSTOMER', 'SEND_INVOICE'
  `description` TEXT,
  `conversation_status` VARCHAR(50) DEFAULT 'NEW', -- e.g., 'NEW', 'PENDING', 'DONE'
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`assigned_user_id`) REFERENCES `staff_users`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`order_id`) ON DELETE SET NULL
);

-- Table to log daily staff login/attendance
CREATE TABLE `attendance` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `login_date` DATE NOT NULL,
  `login_time` TIME NOT NULL,
  UNIQUE KEY `user_day` (`user_id`, `login_date`),
  FOREIGN KEY (`user_id`) REFERENCES `staff_users`(`id`) ON DELETE CASCADE
);

-- Table for email templates
CREATE TABLE `email_templates` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) UNIQUE NOT NULL,
  `subject` VARCHAR(255) NOT NULL,
  `body` TEXT NOT NULL,
  `associated_event` VARCHAR(255) NOT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table for messages within a task (conversation)
CREATE TABLE `conversation_messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `task_id` INT NOT NULL,
  `sender_id` INT NOT NULL,
  `sender_type` ENUM('staff', 'customer') NOT NULL,
  `message` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`task_id`) REFERENCES `tasks`(`id`) ON DELETE CASCADE
);