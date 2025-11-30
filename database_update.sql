-- Database Update: Add 'call-request' to signal_type ENUM
-- Run this if you already created the database with the old schema

-- Update signals table to include 'call-request' in signal_type
ALTER TABLE signals 
MODIFY signal_type ENUM('offer', 'answer', 'ice-candidate', 'call-request') NOT NULL;

-- Verify the update
SHOW COLUMNS FROM signals LIKE 'signal_type';

