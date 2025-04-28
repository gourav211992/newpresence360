ALTER TABLE `erp_land_plots` 
ADD COLUMN `document_status` VARCHAR(255) NULL AFTER `status`,
ADD COLUMN `approval_level` INT NOT NULL DEFAULT 1 AFTER `document_status`;




ALTER TABLE erp_stakeholder_interactions DROP COLUMN userable_type;
ALTER TABLE erp_complaint_management DROP COLUMN userable_type;
ALTER TABLE erp_feedback_processes DROP COLUMN userable_type;
ALTER TABLE erp_public_outreach_and_communications DROP COLUMN userable_type;
ALTER TABLE erp_engagement_trackings DROP COLUMN userable_type;
ALTER TABLE erp_investor_relation_management DROP COLUMN userable_type;
ALTER TABLE erp_gov_relation_management DROP COLUMN userable_type;
