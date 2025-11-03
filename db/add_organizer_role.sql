-- Update the users table to include 'organizer' role
-- Run this SQL script in phpMyAdmin after importing sad_db.sql

-- Step 1: Modify the role enum to include 'organizer'
ALTER TABLE `users` 
MODIFY `role` ENUM('administrator','organizer','manager','supplier') NOT NULL;

-- Step 2: Update existing 'manager' users to 'organizer' if they are event coordinators
-- (Optional - only if you want to migrate existing manager users to organizer role)
-- UPDATE `users` SET `role` = 'organizer' WHERE `role` = 'manager';

-- Step 3: Add sample organizer user for testing
INSERT INTO `users` (`username`, `password`, `first_name`, `last_name`, `email`, `phone`, `role`) 
VALUES 
('organizer_juan', '$2y$10$uh3m79DGqHoJ8z/HCo4iluGb18gWzZEj0MT.TaWU9e1l5lDiolBTi', 'Juan', 'Dela Cruz', 'juan@organizer.com', '09171234567', 'organizer');

-- Password for test account is: password123

-- Verify the changes
SELECT user_id, username, first_name, last_name, email, role FROM users;
