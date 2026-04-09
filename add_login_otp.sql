-- Add OTP columns to users table for passwordless login
ALTER TABLE `users`
  ADD COLUMN `login_otp` VARCHAR(6) DEFAULT NULL,
  ADD COLUMN `otp_created_at` TIMESTAMP NULL DEFAULT NULL;

-- Ensure existing users have null values
UPDATE `users` SET login_otp = NULL, otp_created_at = NULL;