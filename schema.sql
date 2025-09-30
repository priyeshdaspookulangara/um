-- Database schema for Siva Ganga Dance Costumes Staff User Manager

-- Table for staff user authentication and data
CREATE TABLE `staff_users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(255) UNIQUE NOT NULL,
  `password` VARCHAR(255) NOT NULL, -- Storing in plain text as requested
  `full_name` VARCHAR(255) NOT NULL,
  `is_active` TINYINT(1) DEFAULT 1
);

-- Table to store tasks for load balancing
CREATE TABLE `tasks` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `assigned_user_id` INT,
  `task_type` VARCHAR(255), -- e.g., 'CONTACT_CUSTOMER', 'SEND_INVOICE'
  `description` TEXT,
  `target_customer` VARCHAR(255),
  `conversation_status` VARCHAR(50) DEFAULT 'NEW', -- e.g., 'NEW', 'PENDING', 'DONE'
  `payment_made` TINYINT(1) DEFAULT 0, -- 0 for No, 1 for Yes
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`assigned_user_id`) REFERENCES `staff_users`(`id`) ON DELETE SET NULL
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

-- Table for customer orders
CREATE TABLE `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `customer_name` VARCHAR(255) NOT NULL,
  `order_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `total_amount` DECIMAL(10, 2) NOT NULL
);

-- Table for items within an order
CREATE TABLE `order_details` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `item_id` VARCHAR(255) NOT NULL, -- e.g., a product SKU
  `quantity` INT NOT NULL,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE
);

-- Add order_id to tasks table
ALTER TABLE `tasks`
ADD COLUMN `order_id` INT NULL,
ADD FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE SET NULL;