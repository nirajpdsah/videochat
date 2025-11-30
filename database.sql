-- VideoChat Database Schema
-- Run this SQL script to create all required tables

-- Users table
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `profile_picture` VARCHAR(255) DEFAULT 'default-avatar.png',
    `status` ENUM('online', 'offline', 'on_call') DEFAULT 'offline',
    `last_seen` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Signals table (for WebRTC signaling)
CREATE TABLE IF NOT EXISTS `signals` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `from_user_id` INT(11) NOT NULL,
    `to_user_id` INT(11) NOT NULL,
    `signal_type` ENUM('offer', 'answer', 'ice-candidate', 'call-request') NOT NULL,
    `signal_data` TEXT NOT NULL,
    `call_type` ENUM('video', 'audio') DEFAULT 'video',
    `is_read` TINYINT(1) DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_to_user` (`to_user_id`, `is_read`),
    INDEX `idx_from_user` (`from_user_id`),
    FOREIGN KEY (`from_user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`to_user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Messages table (for chat functionality)
CREATE TABLE IF NOT EXISTS `messages` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `from_user_id` INT(11) NOT NULL,
    `to_user_id` INT(11) NOT NULL,
    `message` TEXT NOT NULL,
    `is_read` TINYINT(1) DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_conversation` (`from_user_id`, `to_user_id`),
    INDEX `idx_to_user` (`to_user_id`, `is_read`),
    FOREIGN KEY (`from_user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`to_user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

