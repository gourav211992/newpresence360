ALTER TABLE `erp_ledgers`  
ADD COLUMN `tax_type` VARCHAR(255) NULL AFTER `ledger_group_id`,  
ADD COLUMN `tax_percentage` DECIMAL(10,2) NULL AFTER `tax_type`;
