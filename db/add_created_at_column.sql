-- Add created_at column to events table for tracking booking timestamps
-- Run this SQL script in phpMyAdmin

-- Add created_at column if it doesn't exist
ALTER TABLE `events` 
ADD COLUMN `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER `venue_id`;

-- Update existing records with event_date as created_at (if needed)
UPDATE `events` SET `created_at` = `event_date` WHERE `created_at` IS NULL;

-- Verify the changes
SELECT event_id, event_name, event_date, created_at, status FROM events LIMIT 5;
