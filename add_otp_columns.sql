-- Add OTP and verification columns to orders table
ALTER TABLE `orders` 
ADD COLUMN `otp` VARCHAR(6) DEFAULT NULL,
ADD COLUMN `otp_verified` TINYINT(1) DEFAULT 0,
ADD COLUMN `otp_created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- If you want to update existing orders (optional)
UPDATE `orders` SET otp_verified = 0 WHERE otp_verified IS NULL;
