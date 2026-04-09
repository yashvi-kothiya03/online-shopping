-- Add seller mapping columns required for seller panel filtering
-- Safe to run multiple times on MySQL/MariaDB versions that support IF NOT EXISTS

ALTER TABLE `products`
ADD COLUMN IF NOT EXISTS `seller_id` INT NULL DEFAULT NULL;

ALTER TABLE `orders`
ADD COLUMN IF NOT EXISTS `seller_ids` VARCHAR(255) NULL DEFAULT NULL;
