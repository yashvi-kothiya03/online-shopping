-- Add order item-level payload so sellers can remove only their own items from mixed orders
ALTER TABLE `orders`
ADD COLUMN IF NOT EXISTS `order_items_json` LONGTEXT NULL;
