-- Manual Migration: Update stocks table - Remove product relation, add direct fields
-- Run this in HeidiSQL or phpMyAdmin

USE si_majter;

-- Add new columns for direct product information
ALTER TABLE `stocks` 
ADD COLUMN `product_code` VARCHAR(100) NULL AFTER `company_id`,
ADD COLUMN `product_name` VARCHAR(255) NOT NULL AFTER `product_code`,
ADD COLUMN `product_type` ENUM('Local', 'Import') NOT NULL DEFAULT 'Local' AFTER `product_name`,
ADD COLUMN `unit` VARCHAR(50) NOT NULL DEFAULT 'pcs' AFTER `product_type`,
ADD COLUMN `category` VARCHAR(100) NULL AFTER `unit`,
ADD COLUMN `base_price` DECIMAL(15, 2) NOT NULL DEFAULT 0.00 AFTER `category`;

-- Make product_id nullable (for backward compatibility)
ALTER TABLE `stocks` 
MODIFY COLUMN `product_id` CHAR(36) NULL;

-- Add indexes for better performance
ALTER TABLE `stocks`
ADD INDEX `stocks_product_code_index` (`product_code`),
ADD INDEX `stocks_product_name_index` (`product_name`),
ADD INDEX `stocks_product_type_index` (`product_type`);

-- Add migration record
INSERT INTO `migrations` (`migration`, `batch`) 
VALUES ('2026_02_10_040000_update_stocks_table_remove_product_relation_add_direct_fields', 
        (SELECT COALESCE(MAX(batch), 0) + 1 FROM (SELECT batch FROM migrations) as temp));

-- Done!
SELECT 'Migration completed successfully!' as status;
