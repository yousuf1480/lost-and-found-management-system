-- =======================================================
-- FINAL DATABASE SCHEMA (LOST & FOUND SYSTEM - PHASE 2)
-- =======================================================

-- 1. Database Banayen
CREATE DATABASE IF NOT EXISTS lost_found CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE lost_found;

-- 2. Users Table
-- Isme mandatory Student ID aur ID Proof upload ka column shamil hai
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    student_id VARCHAR(50) NOT NULL UNIQUE, 
    id_proof VARCHAR(255) DEFAULT NULL,    
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    is_blocked TINYINT(1) DEFAULT 0,        
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Items Table 
-- Reported items (Lost/Found) ka data store karne ke liye
CREATE TABLE IF NOT EXISTS items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('lost', 'found') NOT NULL,
    title VARCHAR(200) NOT NULL,
    category ENUM('electronics', 'bags', 'keys', 'documents', 'clothing', 'jewelry', 'other') DEFAULT 'other',
    description TEXT NOT NULL,
    location VARCHAR(200) NOT NULL,
    date_lost_found DATE NOT NULL,
    image_path VARCHAR(255) DEFAULT NULL,
    status ENUM('open', 'resolved', 'pending') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 4. Messages Table 
-- User-to-User private chat ke liye
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 5. Notifications Table 
-- System Alerts aur Match found hone ki notifications ke liye
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 6. Item Matches Table 
-- Jab do items match hon toh Admin verification ke liye yahan record jata hai
CREATE TABLE IF NOT EXISTS item_matches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lost_item_id INT NOT NULL,
    found_item_id INT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lost_item_id) REFERENCES items(id) ON DELETE CASCADE,
    FOREIGN KEY (found_item_id) REFERENCES items(id) ON DELETE CASCADE
);

-- 7. Default Admin Account Insert Karein
-- Email: admin@university.edu | Password: password
-- Note: Student ID 'ADMIN-001' default rakha gaya hai
INSERT IGNORE INTO users (name, email, student_id, password, role)
VALUES ('Admin', 'admin@university.edu', 'ADMIN-001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');