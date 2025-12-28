-- Fix signal_type enum to include all signal types
ALTER TABLE signals MODIFY COLUMN signal_type ENUM('offer','answer','ice-candidate','call-request','call-accepted','call-rejected','call-ended','receiver-ready') NOT NULL;
