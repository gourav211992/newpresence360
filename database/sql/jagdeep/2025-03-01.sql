--MASTER DB --
INSERT INTO erp_sub_types (name, status) VALUES
('Raw Material', 1),
('WIP/Semi Finished', 1),
('Finished Goods', 1),
('Traded Item', 1),
('Asset', 1),
('Expense', 1);

--ALL DATABASES--
ALTER TABLE `erp_so_items` ADD `delivery_date` DATE NULL DEFAULT NULL AFTER `rate`;
ALTER TABLE `erp_so_items_history` ADD `delivery_date` DATE NULL DEFAULT NULL AFTER `rate`;

ALTER TABLE `erp_mi_items` ADD `pwo_item_id` BIGINT NULL DEFAULT NULL AFTER `mo_item_id`;
ALTER TABLE `erp_mi_items_history` ADD `mo_item_id` BIGINT NULL DEFAULT NULL AFTER `mi_item_id`;
ALTER TABLE `erp_mi_items_history` ADD `pwo_item_id` BIGINT NULL DEFAULT NULL AFTER `mo_item_id`;

ALTER TABLE `erp_pwo_items` ADD `mi_qty` DOUBLE(20,6) NOT NULL DEFAULT '0' AFTER `inventory_uom_qty`;
ALTER TABLE `erp_pwo_items_history` ADD `mi_qty` DOUBLE(20,6) NOT NULL DEFAULT '0' AFTER `inventory_uom_qty`;

ALTER TABLE `erp_pi_items` ADD `mi_qty` DOUBLE(20,6) NOT NULL DEFAULT '0' AFTER `order_qty`;
ALTER TABLE `erp_pi_items_history` ADD `mi_qty` DOUBLE(20,6) NOT NULL DEFAULT '0' AFTER `order_qty`;

#########################################################################################################################################
--Master DB--
CREATE TABLE `erp_organization_types` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NULL,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `erp_organization_types` (`name`, `status`, `created_at`, `updated_at`) VALUES
('Public Limited', 'active', NOW(), NOW()),
('Private Limited', 'active', NOW(), NOW()),
('Proprietor', 'active', NOW(), NOW()),
('Partnership', 'active', NOW(), NOW()),
('Small Enterprise', 'active', NOW(), NOW()),
('Medium Enterprise', 'active', NOW(), NOW());

#############################################################################################################################

--all databases

ALTER TABLE `erp_mi_items` ADD `pi_item_id` BIGINT NULL DEFAULT NULL AFTER `pwo_item_id`;
ALTER TABLE `erp_mi_items_history` ADD `pi_item_id` BIGINT NULL DEFAULT NULL AFTER `pwo_item_id`;


ALTER TABLE `erp_pi_items` ADD `mi_qty` DOUBLE(20,6) NOT NULL DEFAULT '0' AFTER `order_qty`;
ALTER TABLE `erp_pi_items_history` ADD `mi_qty` DOUBLE(20,6) NOT NULL DEFAULT '0' AFTER `order_qty`;