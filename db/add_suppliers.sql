-- Add more comprehensive supplier and service data
-- Run this after the main sad_db.sql

-- Add more suppliers
INSERT INTO `suppliers` (`supplier_name`, `service_category`, `email`, `phone`, `location`, `availability_status`) VALUES
('Gourmet Catering Co.', 'Catering', 'info@gourmetcatering.com', '09171112222', 'Makati City', 'available'),
('Delicious Bites', 'Catering', 'hello@deliciousbites.com', '09182223333', 'Quezon City', 'available'),
('Premium Feast Catering', 'Catering', 'contact@premiumfeast.com', '09193334444', 'Taguig City', 'available'),
('Elite Sound Systems', 'Lights and Sounds', 'elite@soundsystems.com', '09174445555', 'Pasay City', 'available'),
('Stage Master Productions', 'Lights and Sounds', 'info@stagemaster.com', '09185556666', 'Manila City', 'available'),
('Pro Lens Photography', 'Photography', 'prolens@photo.com', '09196667777', 'Makati City', 'available'),
('Timeless Moments Studio', 'Photography', 'timeless@studio.com', '09177778888', 'BGC Taguig', 'available'),
('Elegant Videography', 'Videography', 'elegant@video.com', '09188889999', 'Quezon City', 'available'),
('Cinematic Dreams', 'Videography', 'cinematic@dreams.com', '09199990000', 'Makati City', 'available'),
('The Voice Events', 'Host/Emcee', 'voice@events.com', '09170001111', 'Manila City', 'available'),
('Charisma Hosting Services', 'Host/Emcee', 'charisma@hosting.com', '09181112222', 'Pasay City', 'available'),
('Petals & Blossoms', 'Styling and Flowers', 'petals@flowers.com', '09192223333', 'Makati City', 'available'),
('Dreamy Decorations', 'Styling and Flowers', 'dreamy@decor.com', '09173334444', 'Quezon City', 'available'),
('Party Rental Hub', 'Equipment Rental', 'rental@partyhub.com', '09184445555', 'Taguig City', 'available'),
('Event Essentials', 'Equipment Rental', 'essentials@event.com', '09195556666', 'Makati City', 'available');

-- Add more services linked to suppliers
INSERT INTO `services` (`service_name`, `category`, `description`, `price`, `supplier_id`) VALUES
-- Catering Services
('Budget-Friendly Buffet', 'Catering', 'Basic buffet for up to 100 guests. Includes main course, side dishes, and dessert.', 15000.00, 4),
('Premium Buffet Package', 'Catering', 'Premium buffet with international and local cuisine for up to 150 guests.', 35000.00, 4),
('Deluxe Catering Package', 'Catering', 'Full-service catering with waiters, premium menu, and beverage service.', 50000.00, 5),
('Cocktail Reception Package', 'Catering', 'Finger foods and appetizers with beverage station for 100 guests.', 25000.00, 6),

-- Lights and Sounds
('Premium Audio-Visual Package', 'Lights and Sounds', 'Complete AV setup with LED wall, projector, advanced sound system, and lighting.', 35000.00, 7),
('Wedding Sound Package', 'Lights and Sounds', 'Includes wireless mics, speakers, and ambient lighting for weddings.', 18000.00, 1),
('Concert-Grade Sound System', 'Lights and Sounds', 'Professional concert sound with stage lighting and special effects.', 50000.00, 8),
('Basic PA System', 'Lights and Sounds', 'Simple PA system with microphones for small events.', 8000.00, 7),

-- Photography
('Full-Day Photography', 'Photography', 'Professional photographer with 8-hour coverage and 300+ edited photos.', 30000.00, 9),
('Half-Day Photography', 'Photography', 'Professional photographer with 4-hour coverage and 150+ edited photos.', 18000.00, 2),
('Engagement Shoot Package', 'Photography', 'Pre-event photoshoot at location of choice with 100+ edited photos.', 15000.00, 9),
('Photo & Video Combo', 'Photography', 'Combined photography and videography package with same-day edit.', 55000.00, 10),

-- Videography
('Cinematic Wedding Video', 'Videography', 'Full-day coverage with cinematic edit, drone shots, and same-day edit.', 40000.00, 11),
('Event Highlights Video', 'Videography', 'Short highlights video of your event with professional editing.', 20000.00, 12),
('Live Streaming Service', 'Videography', 'Professional live streaming setup for virtual guests.', 15000.00, 11),

-- Host/Emcee
('Professional Wedding Host', 'Host/Emcee', 'Experienced wedding host to manage ceremony and reception flow.', 12000.00, 13),
('Corporate Event Emcee', 'Host/Emcee', 'Professional emcee for corporate events, seminars, and conferences.', 15000.00, 14),
('Party Host & Entertainer', 'Host/Emcee', 'Energetic host for birthday parties and celebrations with games.', 8000.00, 13),

-- Styling and Flowers
('Premium Floral Arrangements', 'Styling and Flowers', 'Luxury floral styling with centerpieces, bouquet, and ceremony decor.', 35000.00, 15),
('Garden Wedding Styling', 'Styling and Flowers', 'Complete garden wedding setup with flowers, draping, and styling.', 28000.00, 3),
('Corporate Event Styling', 'Styling and Flowers', 'Modern professional styling for corporate events.', 22000.00, 16),
('Birthday Theme Decoration', 'Styling and Flowers', 'Customized theme decorations for birthday parties.', 12000.00, 16),

-- Equipment Rental
('Tables & Chairs Package', 'Equipment Rental', 'Complete tables and chairs setup for 100 guests.', 8000.00, 17),
('Tent & Canopy Rental', 'Equipment Rental', 'Large outdoor tent with side panels for 150 guests.', 15000.00, 17),
('Stage & Platform Setup', 'Equipment Rental', 'Professional stage setup with stairs and skirting.', 12000.00, 18),
('Photo Booth Rental', 'Equipment Rental', 'Photo booth with props, prints, and attendant for 4 hours.', 10000.00, 18);
