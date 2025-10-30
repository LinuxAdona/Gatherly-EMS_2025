-- Enhanced Database Schema for Gatherly EMS with AI Recommendations
-- Add this to your existing sad_db database

-- Add additional columns to venues table for better recommendations
ALTER TABLE `venues` 
ADD COLUMN `latitude` DECIMAL(10, 8) DEFAULT NULL AFTER `location`,
ADD COLUMN `longitude` DECIMAL(11, 8) DEFAULT NULL AFTER `latitude`,
ADD COLUMN `parking_capacity` INT DEFAULT 0 AFTER `longitude`,
ADD COLUMN `has_stage_setup` BOOLEAN DEFAULT FALSE AFTER `parking_capacity`,
ADD COLUMN `accessibility_features` TEXT DEFAULT NULL AFTER `has_stage_setup`,
ADD COLUMN `venue_type` VARCHAR(50) DEFAULT 'indoor' AFTER `accessibility_features`,
ADD COLUMN `catering_available` BOOLEAN DEFAULT FALSE AFTER `venue_type`,
ADD COLUMN `sound_system_available` BOOLEAN DEFAULT FALSE AFTER `catering_available`,
ADD COLUMN `total_bookings` INT DEFAULT 0 AFTER `sound_system_available`,
ADD COLUMN `average_rating` DECIMAL(3,2) DEFAULT 0.00 AFTER `total_bookings`;

-- Update existing venues with coordinates (sample data for Metro Manila)
UPDATE `venues` SET 
    `latitude` = 14.5547, `longitude` = 121.0481, 
    `parking_capacity` = 100, `has_stage_setup` = TRUE,
    `accessibility_features` = 'Wheelchair accessible, Elevator, Ramps',
    `venue_type` = 'indoor', `catering_available` = TRUE,
    `sound_system_available` = TRUE, `total_bookings` = 15,
    `average_rating` = 4.5
WHERE `venue_id` = 1;

UPDATE `venues` SET 
    `latitude` = 14.5243, `longitude` = 121.0293,
    `parking_capacity` = 80, `has_stage_setup` = TRUE,
    `accessibility_features` = 'Wheelchair accessible, Elevator',
    `venue_type` = 'indoor', `catering_available` = TRUE,
    `sound_system_available` = TRUE, `total_bookings` = 12,
    `average_rating` = 4.3
WHERE `venue_id` = 2;

UPDATE `venues` SET 
    `latitude` = 14.6760, `longitude` = 121.0437,
    `parking_capacity` = 50, `has_stage_setup` = FALSE,
    `accessibility_features` = 'Ground level access, Wide pathways',
    `venue_type` = 'outdoor', `catering_available` = TRUE,
    `sound_system_available` = FALSE, `total_bookings` = 18,
    `average_rating` = 4.7
WHERE `venue_id` = 3;

UPDATE `venues` SET 
    `latitude` = 14.5378, `longitude` = 120.9984,
    `parking_capacity` = 120, `has_stage_setup` = TRUE,
    `accessibility_features` = 'Wheelchair accessible, Elevator, Ramps, Reserved parking',
    `venue_type` = 'outdoor', `catering_available` = TRUE,
    `sound_system_available` = TRUE, `total_bookings` = 10,
    `average_rating` = 4.6
WHERE `venue_id` = 4;

-- Create table for venue booking history (for collaborative filtering)
CREATE TABLE IF NOT EXISTS `venue_bookings_history` (
  `booking_history_id` INT(11) NOT NULL AUTO_INCREMENT,
  `venue_id` INT(11) NOT NULL,
  `event_type` VARCHAR(50) NOT NULL,
  `guest_count` INT(11) NOT NULL,
  `budget_range` VARCHAR(50) NOT NULL,
  `booking_date` DATE NOT NULL,
  `season` ENUM('peak', 'off-peak') DEFAULT 'off-peak',
  `satisfaction_score` DECIMAL(3,2) DEFAULT NULL,
  PRIMARY KEY (`booking_history_id`),
  KEY `venue_id` (`venue_id`),
  FOREIGN KEY (`venue_id`) REFERENCES `venues`(`venue_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert sample booking history for collaborative filtering
INSERT INTO `venue_bookings_history` 
(`venue_id`, `event_type`, `guest_count`, `budget_range`, `booking_date`, `season`, `satisfaction_score`) VALUES
(1, 'Wedding', 150, '80000-100000', '2025-01-15', 'off-peak', 4.8),
(1, 'Wedding', 200, '80000-100000', '2025-02-20', 'peak', 4.6),
(2, 'Corporate', 200, '90000-110000', '2025-02-10', 'off-peak', 4.5),
(2, 'Corporate', 180, '90000-110000', '2025-03-15', 'peak', 4.4),
(3, 'Birthday', 100, '50000-70000', '2025-03-25', 'peak', 4.9),
(3, 'Corporate', 80, '40000-60000', '2025-05-12', 'off-peak', 4.7),
(4, 'Concert', 300, '110000-130000', '2025-04-18', 'peak', 4.8);

-- Create table for AI chat messages
CREATE TABLE IF NOT EXISTS `ai_chat_messages` (
  `message_id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `message` TEXT NOT NULL,
  `response` TEXT NOT NULL,
  `context_data` JSON DEFAULT NULL,
  `timestamp` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`message_id`),
  KEY `user_id` (`user_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create table for demand tracking (for dynamic pricing)
CREATE TABLE IF NOT EXISTS `venue_demand_log` (
  `demand_log_id` INT(11) NOT NULL AUTO_INCREMENT,
  `venue_id` INT(11) NOT NULL,
  `inquiry_date` DATE NOT NULL,
  `inquiry_count` INT DEFAULT 1,
  `booking_count` INT DEFAULT 0,
  PRIMARY KEY (`demand_log_id`),
  UNIQUE KEY `venue_date` (`venue_id`, `inquiry_date`),
  KEY `venue_id` (`venue_id`),
  FOREIGN KEY (`venue_id`) REFERENCES `venues`(`venue_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create table for price optimization history
CREATE TABLE IF NOT EXISTS `pricing_history` (
  `pricing_history_id` INT(11) NOT NULL AUTO_INCREMENT,
  `venue_id` INT(11) NOT NULL,
  `date` DATE NOT NULL,
  `calculated_price` DECIMAL(10,2) NOT NULL,
  `demand_multiplier` DECIMAL(4,2) DEFAULT 1.00,
  `season_multiplier` DECIMAL(4,2) DEFAULT 1.00,
  `day_type_multiplier` DECIMAL(4,2) DEFAULT 1.00,
  `occupancy_rate` DECIMAL(5,2) DEFAULT 0.00,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`pricing_history_id`),
  KEY `venue_id` (`venue_id`),
  KEY `date` (`date`),
  FOREIGN KEY (`venue_id`) REFERENCES `venues`(`venue_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Add columns to recommendations table for better tracking
ALTER TABLE `recommendations`
ADD COLUMN `capacity_score` DECIMAL(5,2) DEFAULT NULL AFTER `suitability_score`,
ADD COLUMN `price_score` DECIMAL(5,2) DEFAULT NULL AFTER `capacity_score`,
ADD COLUMN `location_score` DECIMAL(5,2) DEFAULT NULL AFTER `price_score`,
ADD COLUMN `amenities_score` DECIMAL(5,2) DEFAULT NULL AFTER `location_score`,
ADD COLUMN `availability_score` DECIMAL(5,2) DEFAULT NULL AFTER `amenities_score`,
ADD COLUMN `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER `reason`;

-- Add venue manager role and update chat table
ALTER TABLE `users` 
MODIFY COLUMN `role` ENUM('administrator', 'coordinator', 'client', 'venue_manager') NOT NULL;

-- Add venue manager relationship
ALTER TABLE `venues`
ADD COLUMN `manager_id` INT(11) DEFAULT NULL AFTER `venue_id`,
ADD FOREIGN KEY (`manager_id`) REFERENCES `users`(`user_id`) ON DELETE SET NULL;

-- Enhance chat table for venue-client communication
ALTER TABLE `chat`
ADD COLUMN `venue_id` INT(11) DEFAULT NULL AFTER `event_id`,
ADD COLUMN `is_read` BOOLEAN DEFAULT FALSE AFTER `message_text`,
ADD COLUMN `attachment_url` VARCHAR(255) DEFAULT NULL AFTER `is_read`,
ADD FOREIGN KEY (`venue_id`) REFERENCES `venues`(`venue_id`) ON DELETE CASCADE;

-- Create table for contract templates
CREATE TABLE IF NOT EXISTS `contract_templates` (
  `template_id` INT(11) NOT NULL AUTO_INCREMENT,
  `template_name` VARCHAR(100) NOT NULL,
  `template_content` TEXT NOT NULL,
  `event_type` VARCHAR(50) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert default contract template
INSERT INTO `contract_templates` (`template_name`, `template_content`, `event_type`) VALUES
('Standard Event Contract', 
'EVENT MANAGEMENT AGREEMENT

This agreement is made on {{contract_date}} between:

VENUE: {{venue_name}}
Address: {{venue_location}}
Contact: {{venue_contact}}

CLIENT: {{client_name}}
Email: {{client_email}}
Phone: {{client_phone}}

EVENT DETAILS:
Event Name: {{event_name}}
Event Type: {{event_type}}
Event Date: {{event_date}}
Expected Guests: {{guest_count}}
Venue Capacity: {{venue_capacity}}

FINANCIAL TERMS:
Base Venue Fee: ₱{{base_price}}
Total Cost: ₱{{total_cost}}
Deposit (50%): ₱{{deposit_amount}}
Balance: ₱{{balance_amount}}

Payment Schedule:
- Deposit due: {{deposit_due_date}}
- Final payment due: {{final_payment_date}}

VENUE AMENITIES INCLUDED:
{{amenities_list}}

TERMS AND CONDITIONS:
1. The client agrees to pay the deposit within 7 days of signing this contract.
2. Final payment must be received 14 days before the event date.
3. Cancellation 30+ days before: Full refund minus 20% processing fee
4. Cancellation 15-29 days before: 50% refund
5. Cancellation less than 14 days: No refund
6. The venue reserves the right to cancel in case of force majeure.
7. Client is responsible for any damages to the venue property.
8. Maximum capacity must not be exceeded.

SIGNATURES:
Venue Manager: _________________ Date: _______
Client: _________________ Date: _______

Generated by Gatherly EMS on {{generated_date}}', 
'All');

-- Create indexes for better query performance
CREATE INDEX idx_venues_location ON venues(latitude, longitude);
CREATE INDEX idx_venues_capacity ON venues(capacity);
CREATE INDEX idx_venues_price ON venues(base_price);
CREATE INDEX idx_events_date ON events(event_date);
CREATE INDEX idx_events_type ON events(event_type);
CREATE INDEX idx_bookings_event_type ON venue_bookings_history(event_type);
CREATE INDEX idx_demand_log_date ON venue_demand_log(inquiry_date);
