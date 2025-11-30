-- Migration: Add is_read column to signals table
-- Run this SQL in Railway MySQL console if you get "Undefined array key 'is_read'" errors

-- Check if column exists before adding
ALTER TABLE signals 
ADD COLUMN IF NOT EXISTS `is_read` TINYINT(1) DEFAULT 0 AFTER `call_type`;

-- Add index for better performance
CREATE INDEX IF NOT EXISTS `idx_to_user_read` ON signals (`to_user_id`, `is_read`);

-- Update all existing signals to unread
UPDATE signals SET is_read = 0 WHERE is_read IS NULL;

-- Verify the column was added
SHOW COLUMNS FROM signals;
