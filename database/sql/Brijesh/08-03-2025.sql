-- Add Columns in erp_pr_details table
ALTER TABLE `erp_pr_details`
ADD `item_attributes` LONGTEXT NULL AFTER `item_id`;
