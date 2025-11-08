-- Add missing columns to users table
ALTER TABLE `users` 
ADD COLUMN `status` ENUM('active', 'inactive') DEFAULT 'active' AFTER `role`,
ADD COLUMN `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER `status`;

-- Update existing users to have active status
UPDATE `users` SET `status` = 'active' WHERE `status` IS NULL;

-- Add missing columns to venues table
ALTER TABLE `venues`
ADD COLUMN `manager_id` INT(11) NULL AFTER `venue_id`,
ADD COLUMN `status` ENUM('active', 'inactive') DEFAULT 'active' AFTER `image`,
ADD COLUMN `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER `status`;

-- Update existing venues to have active status
UPDATE `venues` SET `status` = 'active' WHERE `status` IS NULL;

-- Add foreign key for manager_id (optional)
-- ALTER TABLE `venues` ADD FOREIGN KEY (`manager_id`) REFERENCES `users`(`user_id`) ON DELETE SET NULL;
