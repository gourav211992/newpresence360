ALTER TABLE `erp_customers` 
ADD COLUMN `enter_company_org_id` BIGINT UNSIGNED NULL AFTER `company_id`, 
ADD INDEX `enter_company_org_id_index` (`enter_company_org_id`);

ALTER TABLE `erp_vendors` 
ADD COLUMN `enter_company_org_id` BIGINT UNSIGNED NULL AFTER `company_id`, 
ADD INDEX `enter_company_org_id_index` (`enter_company_org_id`);
