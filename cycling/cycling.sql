-- Cit-E Cycling Database Schema
-- Run this file to set up the database

CREATE DATABASE IF NOT EXISTS cycling CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE cycling;

-- Users table (admin accounts)
CREATE TABLE IF NOT EXISTS user (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

-- Admin credentials: username=admin / password=password123
INSERT INTO user (username, password) VALUES ('admin', '$2y$10$Lhbt4gFHOithtzHc5xB4yuYo3URmwalj00He.HZRPoVb4Wm198UmK');
-- Note: the hash above corresponds to "password123" – generated with password_hash()

-- Clubs table
CREATE TABLE IF NOT EXISTS club (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    club_name VARCHAR(150) NOT NULL
) ENGINE=InnoDB;

INSERT INTO club (club_name) VALUES
    ('Velocity Riders'),
    ('Peak Pedallers'),
    ('Urban Cyclists'),
    ('Sprint Squad'),
    ('Chain Reaction');

-- Participants table
CREATE TABLE IF NOT EXISTS participant (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    age INT UNSIGNED NOT NULL,
    gender VARCHAR(20) NOT NULL,
    power_output DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    distance DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    club_id INT UNSIGNED,
    FOREIGN KEY (club_id) REFERENCES club(id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO participant (first_name, last_name, age, gender, power_output, distance, club_id) VALUES
    ('Alice',   'Smith',    28, 'Female', 250.50, 42.30, 1),
    ('Bob',     'Jones',    35, 'Male',   310.75, 55.10, 1),
    ('Carol',   'White',    22, 'Female', 195.00, 38.60, 2),
    ('David',   'Brown',    41, 'Male',   280.20, 48.90, 2),
    ('Eve',     'Taylor',   30, 'Female', 220.40, 40.00, 3),
    ('Frank',   'Wilson',   26, 'Male',   340.00, 60.50, 3),
    ('Grace',   'Moore',    33, 'Female', 205.80, 37.20, 4),
    ('Henry',   'Clark',    45, 'Male',   260.60, 50.75, 4),
    ('Isla',    'Lewis',    19, 'Female', 180.30, 35.00, 5),
    ('Jack',    'Walker',   38, 'Male',   295.90, 52.40, 5),
    ('Karen',   'Hall',     27, 'Female', 230.10, 44.80, NULL),
    ('Liam',    'Allen',    31, 'Male',   315.45, 57.00, NULL);

-- Interest registration table
CREATE TABLE IF NOT EXISTS interest (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    city VARCHAR(100) NOT NULL,
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
