-- Create the database if it doesn't exist
CREATE DATABASE IF NOT EXISTS tickfly;

-- Use the database
USE tickfly;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    remember_me TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add indexes for faster searches
CREATE INDEX idx_username ON users (username);
CREATE INDEX idx_email ON users (email);

ALTER TABLE users ADD COLUMN role VARCHAR(20) NOT NULL DEFAULT 'user';

-- Update admin user
UPDATE users SET role = 'admin' WHERE username = 'admin';

-- Update CS user
UPDATE users SET role = 'cs' WHERE username = 'cs';

-- Add admin user with hashed password 'admin'
INSERT INTO users (username, email, phone, password, role) 
VALUES ('admin', 'admin@tickfly.com', '123456', '$2y$10$oDuJWaPwZnVoKUFOsVMN0.40W6K3YJLGrkNcEXQQXHg8S5eKIioKa', 'admin');

-- Add CS user with hashed password 'cs'
INSERT INTO users (username, email, phone, password, role) 
VALUES ('cs', 'cs@tickfly.com', '123457', '$2y$10$l.JXHffhsNXs/JLPyFm9d.UlHUH9kbKcsn8h2YFvbCFM.oXYnR.6O', 'cs');

-- Add ticket table
CREATE TABLE ticket (
    id INT PRIMARY KEY AUTO_INCREMENT,
    booking_code VARCHAR(100),
    username VARCHAR(100),
    email VARCHAR(50),
    phone BIGINT,
    flight VARCHAR(100),
    flight_date DATE,
    departure_time TIME,
    arrival_time TIME,
    origin VARCHAR(100),
    destination VARCHAR(100),
    passenger_title VARCHAR(100),
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    travelers INT,
    seat VARCHAR(100),
    price BIGINT,
    meals TINYINT(1),
    baggage TINYINT(1),
    refundable TINYINT(1),      
    FOREIGN KEY (username) REFERENCES users(username)
);