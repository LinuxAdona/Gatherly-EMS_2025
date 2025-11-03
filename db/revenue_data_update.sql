-- Additional Revenue Data for Dynamic Charts
-- Run this after importing sad_db.sql
-- This adds more events across different months and years for revenue analysis

-- Clear existing events (optional - comment out if you want to keep existing data)
-- TRUNCATE TABLE events;

-- Insert events from 2020-2025 with varied dates for revenue tracking
INSERT INTO `events` (`event_id`, `event_name`, `event_type`, `theme`, `expected_guests`, `total_cost`, `event_date`, `status`, `client_id`, `coordinator_id`, `venue_id`) VALUES
-- 2020 Events
(11, 'New Year Gala 2020', 'Corporate', 'Celebration', 250, 125000.00, '2020-01-15 18:00:00', 'completed', 3, 2, 1),
(12, 'Valentine Wedding', 'Wedding', 'Romance', 180, 95000.00, '2020-02-14 16:00:00', 'completed', 3, 2, 2),
(13, 'Spring Conference', 'Corporate', 'Business', 200, 85000.00, '2020-03-20 09:00:00', 'completed', 3, 2, 3),
(14, 'Easter Celebration', 'Birthday', 'Spring Garden', 120, 65000.00, '2020-04-10 14:00:00', 'completed', 3, 2, 1),
(15, 'Summer Kickoff', 'Concert', 'Beach Party', 300, 150000.00, '2020-07-05 19:00:00', 'completed', 3, 2, 4),
(16, 'Corporate Retreat', 'Corporate', 'Team Building', 100, 75000.00, '2020-09-12 10:00:00', 'completed', 3, 2, 3),
(17, 'Halloween Party', 'Birthday', 'Spooky', 150, 80000.00, '2020-10-31 20:00:00', 'completed', 3, 2, 2),
(18, 'Christmas Gala', 'Corporate', 'Winter Wonderland', 280, 135000.00, '2020-12-20 18:00:00', 'completed', 3, 2, 1),

-- 2021 Events
(19, 'New Year Celebration 2021', 'Corporate', 'Fresh Start', 200, 110000.00, '2021-01-10 19:00:00', 'completed', 3, 2, 2),
(20, 'Love is in the Air', 'Wedding', 'Romantic', 160, 88000.00, '2021-02-20 15:00:00', 'completed', 3, 2, 1),
(21, 'Spring Fashion Show', 'Corporate', 'Elegant', 220, 98000.00, '2021-03-15 18:00:00', 'completed', 3, 2, 4),
(22, 'April Birthday Bash', 'Birthday', 'Colorful', 90, 55000.00, '2021-04-25 17:00:00', 'completed', 3, 2, 3),
(23, 'Mid-Year Summit', 'Corporate', 'Professional', 180, 92000.00, '2021-06-18 09:00:00', 'completed', 3, 2, 2),
(24, 'Summer Music Festival', 'Concert', 'Vibrant', 350, 180000.00, '2021-07-22 19:00:00', 'completed', 3, 2, 4),
(25, 'Back to Business', 'Corporate', 'Modern', 150, 82000.00, '2021-09-08 10:00:00', 'completed', 3, 2, 3),
(26, 'Autumn Wedding', 'Wedding', 'Fall Colors', 200, 105000.00, '2021-10-15 16:00:00', 'completed', 3, 2, 1),
(27, 'Year End Party 2021', 'Corporate', 'Celebration', 240, 128000.00, '2021-12-18 19:00:00', 'completed', 3, 2, 2),

-- 2022 Events
(28, 'January Kickoff 2022', 'Corporate', 'Goals', 190, 95000.00, '2022-01-20 10:00:00', 'completed', 3, 2, 3),
(29, 'Sweetheart Wedding', 'Wedding', 'Love Story', 175, 92000.00, '2022-02-12 16:00:00', 'completed', 3, 2, 1),
(30, 'Tech Conference', 'Corporate', 'Innovation', 250, 115000.00, '2022-03-28 09:00:00', 'completed', 3, 2, 4),
(31, 'Spring Gala', 'Corporate', 'Elegance', 200, 102000.00, '2022-04-15 18:00:00', 'completed', 3, 2, 2),
(32, 'May Day Celebration', 'Birthday', 'Garden Party', 110, 68000.00, '2022-05-01 15:00:00', 'completed', 3, 2, 3),
(33, 'Summer Solstice Concert', 'Concert', 'Sunset', 320, 165000.00, '2022-06-21 19:00:00', 'completed', 3, 2, 4),
(34, 'July Wedding Extravaganza', 'Wedding', 'Grand', 220, 118000.00, '2022-07-16 17:00:00', 'completed', 3, 2, 1),
(35, 'Corporate Anniversary', 'Corporate', 'Milestone', 180, 95000.00, '2022-09-25 18:00:00', 'completed', 3, 2, 2),
(36, 'October Fest', 'Birthday', 'Bavarian', 140, 78000.00, '2022-10-20 19:00:00', 'completed', 3, 2, 3),
(37, 'Holiday Spectacular', 'Concert', 'Christmas', 300, 155000.00, '2022-12-15 19:00:00', 'completed', 3, 2, 4),

-- 2023 Events
(38, 'New Beginnings 2023', 'Corporate', 'Fresh', 210, 108000.00, '2023-01-14 18:00:00', 'completed', 3, 2, 2),
(39, 'Valentine Gala', 'Birthday', 'Love', 130, 72000.00, '2023-02-14 19:00:00', 'completed', 3, 2, 1),
(40, 'March Madness', 'Corporate', 'Sports', 190, 98000.00, '2023-03-22 17:00:00', 'completed', 3, 2, 3),
(41, 'Spring Wedding Bliss', 'Wedding', 'Floral', 195, 105000.00, '2023-04-08 16:00:00', 'completed', 3, 2, 1),
(42, 'May Corporate Summit', 'Corporate', 'Leadership', 220, 112000.00, '2023-05-19 09:00:00', 'completed', 3, 2, 4),
(43, 'Summer Concert Series', 'Concert', 'Rock', 340, 175000.00, '2023-07-28 20:00:00', 'completed', 3, 2, 4),
(44, 'August Birthday Party', 'Birthday', 'Tropical', 100, 62000.00, '2023-08-12 18:00:00', 'completed', 3, 2, 2),
(45, 'Fall Business Expo', 'Corporate', 'Exhibition', 260, 132000.00, '2023-09-30 10:00:00', 'completed', 3, 2, 3),
(46, 'Halloween Masquerade', 'Birthday', 'Mysterious', 180, 92000.00, '2023-10-31 20:00:00', 'completed', 3, 2, 2),
(47, 'November Wedding', 'Wedding', 'Elegant', 170, 95000.00, '2023-11-18 15:00:00', 'completed', 3, 2, 1),
(48, 'Holiday Party 2023', 'Corporate', 'Festive', 250, 130000.00, '2023-12-22 19:00:00', 'completed', 3, 2, 2),

-- 2024 Events
(49, 'January Business Launch', 'Corporate', 'Innovation', 200, 105000.00, '2024-01-25 10:00:00', 'completed', 3, 2, 3),
(50, 'February Romance', 'Wedding', 'Love', 185, 98000.00, '2024-02-16 16:00:00', 'completed', 3, 2, 1),
(51, 'Spring Conference 2024', 'Corporate', 'Growth', 230, 118000.00, '2024-03-12 09:00:00', 'completed', 3, 2, 4),
(52, 'April Showers Gala', 'Birthday', 'Garden', 120, 70000.00, '2024-04-20 18:00:00', 'completed', 3, 2, 2),
(53, 'May Day Wedding', 'Wedding', 'Spring', 190, 102000.00, '2024-05-11 15:00:00', 'completed', 3, 2, 1),
(54, 'Mid-Year Concert', 'Concert', 'Pop', 310, 162000.00, '2024-06-28 19:00:00', 'completed', 3, 2, 4),
(55, 'July Corporate Retreat', 'Corporate', 'Team', 150, 85000.00, '2024-07-15 10:00:00', 'completed', 3, 2, 3),
(56, 'August Birthday Bash', 'Birthday', 'Beach', 140, 78000.00, '2024-08-24 17:00:00', 'completed', 3, 2, 2),
(57, 'September Elegance', 'Wedding', 'Classic', 205, 112000.00, '2024-09-14 16:00:00', 'completed', 3, 2, 1),
(58, 'October Conference', 'Corporate', 'Professional', 240, 125000.00, '2024-10-18 09:00:00', 'completed', 3, 2, 4),
(59, 'November Thanksgiving', 'Birthday', 'Harvest', 160, 88000.00, '2024-11-28 18:00:00', 'completed', 3, 2, 3),
(60, 'December Holiday Gala', 'Corporate', 'Winter', 270, 140000.00, '2024-12-20 19:00:00', 'completed', 3, 2, 2),

-- 2025 Events (Current Year)
(61, 'January Celebration 2025', 'Corporate', 'New Year', 215, 112000.00, '2025-01-18 18:00:00', 'completed', 3, 2, 2),
(62, 'February Love Fest', 'Wedding', 'Romantic', 180, 96000.00, '2025-02-22 16:00:00', 'completed', 3, 2, 1),
(63, 'March Business Summit', 'Corporate', 'Strategy', 225, 115000.00, '2025-03-16 09:00:00', 'completed', 3, 2, 3),
(64, 'April Spring Wedding', 'Wedding', 'Floral Garden', 195, 105000.00, '2025-04-12 15:00:00', 'completed', 3, 2, 1),
(65, 'May Tech Expo', 'Corporate', 'Technology', 250, 128000.00, '2025-05-20 10:00:00', 'completed', 3, 2, 4),
(66, 'June Summer Bash', 'Birthday', 'Tropical', 130, 75000.00, '2025-06-15 18:00:00', 'completed', 3, 2, 2),
(67, 'July Concert Night', 'Concert', 'Music', 320, 168000.00, '2025-07-25 20:00:00', 'completed', 3, 2, 4),
(68, 'August Corporate Gala', 'Corporate', 'Elegant', 200, 108000.00, '2025-08-30 19:00:00', 'completed', 3, 2, 3),
(69, 'September Wedding Dream', 'Wedding', 'Romantic', 210, 115000.00, '2025-09-20 16:00:00', 'completed', 3, 2, 1),
(70, 'October Birthday Party', 'Birthday', 'Autumn', 145, 82000.00, '2025-10-25 18:00:00', 'completed', 3, 2, 2);

-- Note: Update AUTO_INCREMENT to continue from highest ID
ALTER TABLE `events` AUTO_INCREMENT = 71;
