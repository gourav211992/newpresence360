ALTER TABLE `erp_land_parcels_history`  
ADD COLUMN `book_id` BIGINT UNSIGNED NULL AFTER `source_id`,  
ADD COLUMN `document_date` DATE NULL AFTER `document_no`,  
ADD COLUMN `doc_number_type` ENUM('Auto', 'Manually') NOT NULL DEFAULT 'Manually' AFTER `document_date`,  
ADD COLUMN `doc_reset_pattern` ENUM('Never', 'Yearly', 'Quarterly', 'Monthly') NULL AFTER `doc_number_type`,  
ADD COLUMN `doc_prefix` VARCHAR(255) NULL AFTER `doc_reset_pattern`,  
ADD COLUMN `doc_suffix` VARCHAR(255) NULL AFTER `doc_prefix`,  
ADD COLUMN `doc_no` INT NULL AFTER `doc_suffix`,  
ADD COLUMN `group_id` BIGINT UNSIGNED NULL AFTER `organization_id`,  
ADD COLUMN `company_id` BIGINT UNSIGNED NULL AFTER `group_id`;  

ALTER TABLE `erp_land_parcels`  
DROP COLUMN `comapny_id`;


ALTER TABLE `erp_stakeholder_interactions` ADD COLUMN `created_by` BIGINT UNSIGNED NULL;
ALTER TABLE `erp_complaint_management` ADD COLUMN `created_by` BIGINT UNSIGNED NULL;
ALTER TABLE `erp_feedback_processes` ADD COLUMN `created_by` BIGINT UNSIGNED NULL;
ALTER TABLE `erp_public_outreach_and_communications` ADD COLUMN `created_by` BIGINT UNSIGNED NULL;
ALTER TABLE `erp_engagement_trackings` ADD COLUMN `created_by` BIGINT UNSIGNED NULL;
ALTER TABLE `erp_investor_relation_management` ADD COLUMN `created_by` BIGINT UNSIGNED NULL;
ALTER TABLE `erp_gov_relation_management` ADD COLUMN `created_by` BIGINT UNSIGNED NULL;
