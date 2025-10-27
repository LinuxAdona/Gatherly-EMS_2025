-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 27, 2025 at 04:26 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sad_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `chat`
--

CREATE TABLE `chat` (
  `chat_id` int(11) NOT NULL,
  `event_id` int(11) DEFAULT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `receiver_id` int(11) DEFAULT NULL,
  `message_text` text DEFAULT NULL,
  `timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dynamic_pricing`
--

CREATE TABLE `dynamic_pricing` (
  `pricing_id` int(11) NOT NULL,
  `venue_id` int(11) NOT NULL,
  `date_range_start` date DEFAULT NULL,
  `date_range_end` date DEFAULT NULL,
  `day_type` enum('weekday','weekend') DEFAULT NULL,
  `demand_multiplier` decimal(4,2) DEFAULT 1.00,
  `price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dynamic_pricing`
--

INSERT INTO `dynamic_pricing` (`pricing_id`, `venue_id`, `date_range_start`, `date_range_end`, `day_type`, `demand_multiplier`, `price`) VALUES
(1, 1, '2025-01-01', '2025-02-28', 'weekend', 1.20, 72000.00),
(2, 2, '2025-03-01', '2025-05-31', 'weekday', 1.10, 46000.00),
(3, 3, '2025-04-01', '2025-04-30', 'weekend', 1.25, 56000.00);

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `event_id` int(11) NOT NULL,
  `event_name` varchar(100) NOT NULL,
  `event_type` varchar(50) DEFAULT NULL,
  `theme` varchar(100) DEFAULT NULL,
  `expected_guests` int(11) DEFAULT NULL,
  `total_cost` decimal(10,2) DEFAULT NULL,
  `event_date` datetime DEFAULT NULL,
  `status` enum('pending','confirmed','completed','canceled') DEFAULT 'pending',
  `client_id` int(11) DEFAULT NULL,
  `coordinator_id` int(11) DEFAULT NULL,
  `venue_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`event_id`, `event_name`, `event_type`, `theme`, `expected_guests`, `total_cost`, `event_date`, `status`, `client_id`, `coordinator_id`, `venue_id`) VALUES
(1, 'Mike & Anna Wedding', 'Wedding', 'Rustic Garden', 150, 85000.00, '2025-01-15 00:00:00', 'completed', 3, 2, 1),
(2, 'ABC Corp Year-End', 'Corporate', 'Modern Gala', 200, 95000.00, '2025-02-10 00:00:00', 'confirmed', 3, 2, 2),
(3, 'Sophia 18th Birthday', 'Birthday', 'Royal Blue', 100, 60000.00, '2025-03-25 00:00:00', 'completed', 3, 2, 3),
(4, 'Charity Concert 2025', 'Concert', 'Hope & Light', 300, 120000.00, '2025-04-18 00:00:00', 'confirmed', 3, 2, 4),
(5, 'Team Building Summit', 'Corporate', 'Tropical Retreat', 80, 45000.00, '2025-05-12 00:00:00', 'completed', 3, 2, 3),
(6, 'Linux & Julie Wedding', 'Wedding', 'Rustic Garden', 200, 85000.00, '2025-01-21 00:00:00', 'completed', 3, 2, 1),
(7, 'QRT Corp Year-End', 'Corporate', 'Modern Gala', 150, 95000.00, '2025-03-19 00:00:00', 'confirmed', 3, 2, 2),
(8, 'Maricris 18th Birthday', 'Birthday', 'Royal Blue', 100, 60000.00, '2025-04-29 00:00:00', 'completed', 3, 2, 3),
(9, 'Sabrina Concert 2025', 'Concert', 'Hope & Light', 200, 120000.00, '2025-04-07 00:00:00', 'confirmed', 3, 2, 4),
(10, 'Team Collab Summit', 'Corporate', 'Tropical Retreat', 100, 45000.00, '2025-05-13 00:00:00', 'completed', 3, 2, 3);

-- --------------------------------------------------------

--
-- Table structure for table `event_contracts`
--

CREATE TABLE `event_contracts` (
  `contract_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `contract_text` text DEFAULT NULL,
  `file_url` varchar(255) DEFAULT NULL,
  `signed_status` enum('pending','approved') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_services`
--

CREATE TABLE `event_services` (
  `event_service_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `price_at_booking` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','booked','canceled') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `recommendations`
--

CREATE TABLE `recommendations` (
  `recommendation_id` int(11) NOT NULL,
  `event_id` int(11) DEFAULT NULL,
  `recommended_venue_id` int(11) DEFAULT NULL,
  `recommended_service_id` int(11) DEFAULT NULL,
  `suitability_score` decimal(5,2) DEFAULT NULL,
  `criteria_weights` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`criteria_weights`)),
  `alternative_rank` int(11) DEFAULT NULL,
  `reason` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recommendations`
--

INSERT INTO `recommendations` (`recommendation_id`, `event_id`, `recommended_venue_id`, `recommended_service_id`, `suitability_score`, `criteria_weights`, `alternative_rank`, `reason`) VALUES
(1, 1, 1, 3, 90.50, '{\"price\":0.3,\"capacity\":0.4,\"style\":0.3}', 1, 'Perfect fit for wedding theme and guest count.'),
(2, 2, 2, 1, 85.20, '{\"price\":0.5,\"capacity\":0.2,\"amenities\":0.3}', 1, 'Ideal setup for corporate gala.'),
(3, 3, 3, 2, 88.75, '{\"price\":0.4,\"location\":0.3,\"aesthetics\":0.3}', 2, 'Recommended for birthday celebration.'),
(4, 4, 4, 1, 86.30, '{\"price\":0.5,\"sound\":0.3,\"capacity\":0.2}', 1, 'Best for concert acoustics.'),
(5, 5, 3, 3, 89.10, '{\"price\":0.3,\"theme\":0.4,\"decor\":0.3}', 1, 'Excellent match for corporate styling.');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `service_id` int(11) NOT NULL,
  `service_name` varchar(100) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `service_duration` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`service_id`, `service_name`, `category`, `description`, `price`, `service_duration`, `supplier_id`) VALUES
(1, 'Basic Sound Package', 'Lights and Sounds', 'Includes speakers, mic, and DJ setup.', 12000.00, NULL, 1),
(2, 'Premium Photography', 'Photography', 'Full-day coverage with edited album.', 25000.00, NULL, 2),
(3, 'Floral Styling', 'Styling and Flowers', 'Full floral and event styling setup.', 18000.00, NULL, 3);

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `supplier_id` int(11) NOT NULL,
  `supplier_name` varchar(100) NOT NULL,
  `service_category` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `availability_status` enum('available','booked') DEFAULT 'available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`supplier_id`, `supplier_name`, `service_category`, `email`, `phone`, `location`, `availability_status`) VALUES
(1, 'Luxe Lights & Sounds', 'Lights and Sounds', 'luxe@suppliers.com', '09172223333', 'Makati City', 'available'),
(2, 'Perfect Shots Photography', 'Photography', 'shots@suppliers.com', '09175556666', 'Quezon City', 'available'),
(3, 'Blooms & Beyond', 'Styling and Flowers', 'blooms@suppliers.com', '09173334444', 'Taguig City', 'available');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('administrator','coordinator','client') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `fullname`, `email`, `phone`, `role`) VALUES
(1, 'admin123', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Admin', 'admin@example.com', '09171234567', 'administrator'),
(2, 'coord_linux', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Linux Adona', 'linux@gmail.com', '09181234567', 'coordinator'),
(3, 'client_dore', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dore Cables', 'dore@gmail.com', '09191234567', 'client'),
(4, 'adrian', '$2y$10$uh3m79DGqHoJ8z/HCo4iluGb18gWzZEj0MT.TaWU9e1l5lDiolBTi', 'Adrian Cornado', 'adrian@gmail.com', '', 'client');

-- --------------------------------------------------------

--
-- Table structure for table `venues`
--

CREATE TABLE `venues` (
  `venue_id` int(11) NOT NULL,
  `venue_name` varchar(100) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `capacity` int(11) DEFAULT NULL,
  `base_price` decimal(10,2) DEFAULT NULL,
  `peak_price` decimal(10,2) DEFAULT NULL,
  `offpeak_price` decimal(10,2) DEFAULT NULL,
  `weekday_price` decimal(10,2) DEFAULT NULL,
  `weekend_price` decimal(10,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `availability_status` enum('available','booked') DEFAULT 'available',
  `image` blob DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `venues`
--

INSERT INTO `venues` (`venue_id`, `venue_name`, `location`, `capacity`, `base_price`, `peak_price`, `offpeak_price`, `weekday_price`, `weekend_price`, `description`, `availability_status`, `image`) VALUES
(1, 'Crystal Hall', 'Taguig City', 300, 50000.00, 65000.00, 40000.00, 48000.00, 60000.00, 'Elegant indoor venue ideal for weddings and corporate events.', 'available', NULL),
(2, 'Aurora Pavilion', 'Makati City', 200, 40000.00, 55000.00, 35000.00, 42000.00, 50000.00, 'Modern glass pavilion with garden access.', 'available', NULL),
(3, 'Emerald Garden', 'Quezon City', 150, 35000.00, 50000.00, 30000.00, 37000.00, 45000.00, 'Outdoor garden venue surrounded by lush greenery.', 'available', NULL),
(4, 'Sunset Veranda', 'Pasay City', 250, 45000.00, 60000.00, 35000.00, 40000.00, 55000.00, 'Seaside view venue perfect for receptions.', 'available', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `venue_amenities`
--

CREATE TABLE `venue_amenities` (
  `venue_amenity_id` int(11) NOT NULL,
  `venue_id` int(11) NOT NULL,
  `amenity_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `venue_amenities`
--

INSERT INTO `venue_amenities` (`venue_amenity_id`, `venue_id`, `amenity_name`) VALUES
(1, 1, 'Air Conditioning'),
(2, 1, 'Stage Lighting'),
(3, 2, 'Parking Space'),
(4, 2, 'Wi-Fi'),
(5, 3, 'Garden Setup'),
(6, 3, 'Outdoor Seating'),
(7, 4, 'Ocean View'),
(8, 4, 'VIP Lounge');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `chat`
--
ALTER TABLE `chat`
  ADD PRIMARY KEY (`chat_id`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `dynamic_pricing`
--
ALTER TABLE `dynamic_pricing`
  ADD PRIMARY KEY (`pricing_id`),
  ADD KEY `venue_id` (`venue_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`event_id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `coordinator_id` (`coordinator_id`),
  ADD KEY `venue_id` (`venue_id`);

--
-- Indexes for table `event_contracts`
--
ALTER TABLE `event_contracts`
  ADD PRIMARY KEY (`contract_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `event_services`
--
ALTER TABLE `event_services`
  ADD PRIMARY KEY (`event_service_id`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `recommendations`
--
ALTER TABLE `recommendations`
  ADD PRIMARY KEY (`recommendation_id`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `recommended_venue_id` (`recommended_venue_id`),
  ADD KEY `recommended_service_id` (`recommended_service_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`service_id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`supplier_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `venues`
--
ALTER TABLE `venues`
  ADD PRIMARY KEY (`venue_id`);

--
-- Indexes for table `venue_amenities`
--
ALTER TABLE `venue_amenities`
  ADD PRIMARY KEY (`venue_amenity_id`),
  ADD KEY `venue_id` (`venue_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `chat`
--
ALTER TABLE `chat`
  MODIFY `chat_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dynamic_pricing`
--
ALTER TABLE `dynamic_pricing`
  MODIFY `pricing_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `event_contracts`
--
ALTER TABLE `event_contracts`
  MODIFY `contract_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_services`
--
ALTER TABLE `event_services`
  MODIFY `event_service_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `recommendations`
--
ALTER TABLE `recommendations`
  MODIFY `recommendation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `service_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `supplier_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `venues`
--
ALTER TABLE `venues`
  MODIFY `venue_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `venue_amenities`
--
ALTER TABLE `venue_amenities`
  MODIFY `venue_amenity_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `chat`
--
ALTER TABLE `chat`
  ADD CONSTRAINT `chat_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chat_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chat_ibfk_3` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `dynamic_pricing`
--
ALTER TABLE `dynamic_pricing`
  ADD CONSTRAINT `dynamic_pricing_ibfk_1` FOREIGN KEY (`venue_id`) REFERENCES `venues` (`venue_id`) ON DELETE CASCADE;

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `events_ibfk_2` FOREIGN KEY (`coordinator_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `events_ibfk_3` FOREIGN KEY (`venue_id`) REFERENCES `venues` (`venue_id`) ON DELETE SET NULL;

--
-- Constraints for table `event_contracts`
--
ALTER TABLE `event_contracts`
  ADD CONSTRAINT `event_contracts_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE;

--
-- Constraints for table `event_services`
--
ALTER TABLE `event_services`
  ADD CONSTRAINT `event_services_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `event_services_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`service_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `event_services_ibfk_3` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`) ON DELETE SET NULL;

--
-- Constraints for table `recommendations`
--
ALTER TABLE `recommendations`
  ADD CONSTRAINT `recommendations_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `recommendations_ibfk_2` FOREIGN KEY (`recommended_venue_id`) REFERENCES `venues` (`venue_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `recommendations_ibfk_3` FOREIGN KEY (`recommended_service_id`) REFERENCES `services` (`service_id`) ON DELETE SET NULL;

--
-- Constraints for table `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `services_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`) ON DELETE SET NULL;

--
-- Constraints for table `venue_amenities`
--
ALTER TABLE `venue_amenities`
  ADD CONSTRAINT `venue_amenities_ibfk_1` FOREIGN KEY (`venue_id`) REFERENCES `venues` (`venue_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
