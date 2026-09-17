-- 1. Users Table (Role-based: admin, owner, user)
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `phone` VARCHAR(15) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'owner', 'user') DEFAULT 'user',
    `is_blocked` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default Admin Account (Password: Admin@Amraj2026)
INSERT INTO `users` (`name`, `email`, `phone`, `password`, `role`) VALUES 
('Main Admin', 'admin@amraj.com', '9999999999', '$2y$10$XyBjbSNdQiFXBp71yTa13uZ7QgJcDtbDCggVkAvn9aVDnVYbEhXJG', 'admin');

-- 1A. Admin Audit Log Table
CREATE TABLE IF NOT EXISTS `admin_audit_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `admin_id` INT NOT NULL,
    `action` VARCHAR(80) NOT NULL,
    `entity_type` VARCHAR(40) NOT NULL,
    `entity_id` INT DEFAULT NULL,
    `summary` VARCHAR(255) NOT NULL,
    `meta_json` TEXT DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `user_agent` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_admin_id` (`admin_id`),
    INDEX `idx_entity` (`entity_type`, `entity_id`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Categories Table
CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL UNIQUE,
    `icon` VARCHAR(100) NOT NULL, -- FontAwesome class name
    `slug` VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Basic Core Categories
INSERT INTO `categories` (`name`, `icon`, `slug`) VALUES
('Doctors & Hospitals', 'fa-user-md', 'doctors-hospitals'),
('Plumber & Electrician', 'fa-tools', 'plumber-electrician'),
('Hotels & Restaurants', 'fa-hotel', 'hotels-restaurants'),
('Schools & Colleges', 'fa-graduation-cap', 'schools-colleges'),
('Salons & Spas', 'fa-cut', 'salons-spas'),
('Shops & Stores', 'fa-shopping-bag', 'shops-stores');

-- 3. Businesses/Shops Table
CREATE TABLE IF NOT EXISTS `businesses` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `owner_id` INT NOT NULL,
    `category_id` INT NOT NULL,
    `title` VARCHAR(150) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `phone` VARCHAR(15) NOT NULL,
    `whatsapp` VARCHAR(15) NOT NULL,
    `address` TEXT NOT NULL,
    `city` VARCHAR(100) NOT NULL,
    `pincode` VARCHAR(10) NOT NULL,
    `image_url` VARCHAR(255) DEFAULT NULL, -- ImageKit Hosted URL will save here
    `timing` VARCHAR(100) DEFAULT '09:00 AM - 08:00 PM',
    `is_premium` TINYINT(1) DEFAULT 0, -- 0 = Normal, 1 = Premium Banner
    `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`owner_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Reviews & Ratings Table
CREATE TABLE IF NOT EXISTS `reviews` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `shop_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `rating` TINYINT NOT NULL CHECK (`rating` >= 1 AND `rating` <= 5),
    `comment` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`shop_id`) REFERENCES `businesses`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_user_review` (`shop_id`, `user_id`) -- One user can review a shop only once
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Bookmarks/Saved Shops Table
CREATE TABLE IF NOT EXISTS `bookmarks` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `shop_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`shop_id`) REFERENCES `businesses`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_bookmark` (`user_id`, `shop_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;