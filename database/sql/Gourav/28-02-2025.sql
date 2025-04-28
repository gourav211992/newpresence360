ALTER TABLE `erp_payment_voucher_details_history`  
ADD COLUMN `reference` VARCHAR(255) AFTER `type`;

ALTER TABLE `erp_payment_vouchers_history`  
ADD COLUMN `document_date` DATE,  
ADD COLUMN `doc_number_type` ENUM('Auto', 'Manually') AFTER `document_date`,  
ADD COLUMN `doc_reset_pattern` ENUM('Never', 'Yearly', 'Quarterly', 'Monthly') AFTER `doc_number_type`,  
ADD COLUMN `doc_prefix` VARCHAR(255) AFTER `doc_reset_pattern`,  
ADD COLUMN `doc_suffix` VARCHAR(255) AFTER `doc_prefix`,  
ADD COLUMN `doc_no` INT AFTER `doc_suffix`,  
ADD COLUMN `ledger_group_id` BIGINT AFTER `doc_no`,  
ADD COLUMN `document_status` VARCHAR(255) AFTER `ledger_group_id`,  
ADD COLUMN `approval_level` INT AFTER `document_status`;
