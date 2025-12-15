-- ==========================================
-- HOTEL RESERVATION SYSTEM DATABASE
-- SQL Schema and Sample Data
-- ==========================================

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS hotel_reservation_db;
USE hotel_reservation_db;

-- ==========================================
-- TABLE: rooms
-- Stores information about hotel rooms
-- ==========================================

CREATE TABLE IF NOT EXISTS rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_name VARCHAR(100) NOT NULL,
    room_type VARCHAR(50) NOT NULL,
    price_per_night DECIMAL(10, 2) NOT NULL,
    description TEXT NOT NULL,
    image_url VARCHAR(255) DEFAULT NULL,
    total_rooms INT NOT NULL DEFAULT 1,
    max_guests INT NOT NULL DEFAULT 2,
    features TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_room_type (room_type),
    INDEX idx_price (price_per_night)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- TABLE: reservations
-- Stores all hotel reservations
-- ==========================================

CREATE TABLE IF NOT EXISTS reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_id INT NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    check_in_date DATE NOT NULL,
    check_out_date DATE NOT NULL,
    guests INT NOT NULL DEFAULT 1,
    special_requests TEXT DEFAULT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    nights INT NOT NULL,
    status ENUM('confirmed', 'pending', 'cancelled') DEFAULT 'confirmed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
    INDEX idx_email (email),
    INDEX idx_phone (phone),
    INDEX idx_dates (check_in_date, check_out_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- SAMPLE DATA: Insert rooms
-- ==========================================

INSERT INTO rooms (room_name, room_type, price_per_night, description, image_url, total_rooms, max_guests, features) VALUES
(
    'Deluxe King Suite',
    'Suite',
    299.00,
    'Spacious suite with king-sized bed, city views, and luxury amenities. Perfect for couples seeking ultimate comfort.',
    'assets/imgs/francesca-saraco-_dS27XGgRyQ-unsplash.jpg',
    5,
    2,
    'King Bed,City View,Mini Bar,WiFi'
),
(
    'Executive Ocean View',
    'Deluxe',
    399.00,
    'Premium ocean-facing room with private balcony, perfect for romantic getaways and special occasions.',
    'assets/imgs/juliana-morales-ramirez-GmW4hfTX0ns-unsplash.jpg',
    3,
    2,
    'Ocean View,Balcony,Jacuzzi,WiFi'
),
(
    'Presidential Suite',
    'Presidential',
    799.00,
    'The ultimate luxury experience with separate living area, dining room, and panoramic city views.',
    'assets/imgs/linus-mimietz-p3UWyaujtQo-unsplash.jpg',
    2,
    4,
    '2 Bedrooms,Living Room,Dining Area,Butler Service'
),
(
    'Garden Villa',
    'Villa',
    349.00,
    'Private villa surrounded by lush gardens, featuring an outdoor seating area and modern amenities.',
    'assets/imgs/runnyrem-LfqmND-hym8-unsplash.jpg',
    4,
    3,
    'Garden Access,Queen Bed,Patio,WiFi'
),
(
    'Modern Twin Room',
    'Standard',
    249.00,
    'Contemporary room with twin beds, ideal for friends or business travelers seeking comfort.',
    'assets/imgs/sara-dubler-Koei_7yYtIo-unsplash.jpg',
    8,
    2,
    'Twin Beds,Work Desk,Coffee Maker,WiFi'
),
(
    'Family Penthouse',
    'Penthouse',
    599.00,
    'Spacious penthouse perfect for families, with multiple bedrooms and a fully equipped kitchenette.',
    'assets/imgs/sidath-vimukthi-60S1280_2i8-unsplash.jpg',
    2,
    6,
    '3 Bedrooms,Kitchenette,Living Area,Terrace'
);

-- ==========================================
-- SAMPLE DATA: Insert test reservations
-- ==========================================

INSERT INTO reservations (room_id, full_name, email, phone, check_in_date, check_out_date, guests, total_price, nights, status) VALUES
(1, 'John Doe', 'john.doe@example.com', '+1-555-0101', '2025-12-20', '2025-12-23', 2, 897.00, 3, 'confirmed'),
(2, 'Jane Smith', 'jane.smith@example.com', '+1-555-0102', '2025-12-22', '2025-12-25', 2, 1197.00, 3, 'confirmed'),
(5, 'Michael Johnson', 'michael.j@example.com', '+1-555-0103', '2025-12-18', '2025-12-20', 2, 498.00, 2, 'pending');

-- ==========================================
-- USEFUL QUERIES FOR TESTING
-- ==========================================

-- View all rooms
-- SELECT * FROM rooms;

-- View all reservations with room details
-- SELECT r.*, ro.room_name, ro.room_type 
-- FROM reservations r 
-- JOIN rooms ro ON r.room_id = ro.id 
-- ORDER BY r.created_at DESC;

-- Check room availability for specific dates
-- SELECT r.*, 
--        (SELECT COUNT(*) FROM reservations res 
--         WHERE res.room_id = r.id 
--         AND res.status != 'cancelled'
--         AND (check_in_date <= '2025-12-25' AND check_out_date >= '2025-12-20')
--        ) as booked_rooms,
--        (r.total_rooms - (SELECT COUNT(*) FROM reservations res 
--         WHERE res.room_id = r.id 
--         AND res.status != 'cancelled'
--         AND (check_in_date <= '2025-12-25' AND check_out_date >= '2025-12-20')
--        )) as available_rooms
-- FROM rooms r;
