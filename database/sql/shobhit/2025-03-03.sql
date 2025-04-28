###############################################################################
-- all databases
ALTER TABLE `erp_stations` ADD `is_consumption` ENUM('yes', 'no') DEFAULT 'yes' AFTER `organization_id`;
ALTER TABLE `erp_boms` ADD `production_route_id` BIGINT UNSIGNED NULL AFTER `production_bom_id`;
ALTER TABLE `erp_boms` ADD CONSTRAINT `erp_boms_production_route_id_foreign` FOREIGN KEY (`production_route_id`) REFERENCES `erp_production_routes`(`id`) ON DELETE CASCADE;
ALTER TABLE `erp_boms_history` ADD `production_route_id` BIGINT UNSIGNED NULL AFTER `production_bom_id`;
ALTER TABLE `erp_boms_history` ADD CONSTRAINT `erp_boms_history_production_route_id_foreign` FOREIGN KEY (`production_route_id`) REFERENCES `erp_production_routes`(`id`) ON DELETE CASCADE;
ALTER TABLE `erp_pwo_so_mapping` ADD `current_level` INT DEFAULT 1 AFTER `inventory_uom_qty`;
ALTER TABLE `erp_pwo_station_consumptions` ADD `level` INT DEFAULT 1 AFTER `mo_value`;
ALTER TABLE `erp_pwo_station_consumptions_history` ADD `level` INT DEFAULT 1 AFTER `mo_value`;
ALTER TABLE `erp_pwo_so_mapping` MODIFY `so_id` BIGINT UNSIGNED NULL, MODIFY `so_item_id` BIGINT UNSIGNED NULL;
ALTER TABLE `erp_pwo_so_mapping` ADD `bom_id` BIGINT UNSIGNED NULL AFTER `so_item_id`, ADD `production_route_id` BIGINT UNSIGNED NULL AFTER `bom_id`;
ALTER TABLE `erp_pwo_so_mapping` ADD CONSTRAINT `erp_pwo_so_mapping_bom_id_foreign` FOREIGN KEY (`bom_id`) REFERENCES `erp_boms`(`id`) ON DELETE CASCADE, ADD CONSTRAINT `erp_pwo_so_mapping_production_route_id_foreign` FOREIGN KEY (`production_route_id`) REFERENCES `erp_production_routes`(`id`) ON DELETE CASCADE;
ALTER TABLE `erp_pwo_so_mapping_history` MODIFY `so_id` BIGINT UNSIGNED NULL, MODIFY `so_item_id` BIGINT UNSIGNED NULL;
ALTER TABLE `erp_pwo_so_mapping_history` ADD `bom_id` BIGINT UNSIGNED NULL AFTER `so_item_id`, ADD `production_route_id` BIGINT UNSIGNED NULL AFTER `bom_id`;
ALTER TABLE `erp_pwo_so_mapping_history` ADD CONSTRAINT `erp_pwo_so_mapping_history_bom_id_foreign` FOREIGN KEY (`bom_id`) REFERENCES `erp_boms_history`(`id`) ON DELETE CASCADE, ADD CONSTRAINT `erp_pwo_so_mapping_history_production_route_id_foreign` FOREIGN KEY (`production_route_id`) REFERENCES `erp_production_routes`(`id`) ON DELETE CASCADE;
ALTER TABLE `erp_purchase_orders` ADD `partial_delivery` ENUM('yes', 'no') DEFAULT 'no' AFTER `supp_invoice_required`;
ALTER TABLE `erp_purchase_orders_history` ADD `partial_delivery` ENUM('yes', 'no') DEFAULT 'no' AFTER `supp_invoice_required`;
ALTER TABLE `erp_mo_bom_mapping` ADD `rate` DOUBLE(20, 6) DEFAULT 0 AFTER `qty`;
ALTER TABLE `erp_mo_bom_mapping_history` ADD `rate` DOUBLE(20, 6) DEFAULT 0 AFTER `qty`;
ALTER TABLE `erp_mo_products` ADD `rate` DOUBLE(20, 6) DEFAULT 0 AFTER `qty`;
ALTER TABLE `erp_mo_products_history` ADD `rate` DOUBLE(20, 6) DEFAULT 0 AFTER `qty`;

CREATE TABLE `erp_pwo_bom_mapping` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `pwo_id` BIGINT UNSIGNED NULL,
    `pwo_mapping_id` BIGINT UNSIGNED NULL,
    `bom_id` BIGINT UNSIGNED NULL,
    `bom_detail_id` BIGINT UNSIGNED NULL,
    `item_id` BIGINT UNSIGNED NULL,
    `item_code` VARCHAR(255) NULL,
    `attributes` JSON NULL,
    `uom_id` BIGINT UNSIGNED NULL,
    `qty` DOUBLE(20, 6) DEFAULT 0,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    -- Foreign Keys
    FOREIGN KEY (`pwo_id`) REFERENCES `erp_production_work_orders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`bom_id`) REFERENCES `erp_boms`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`bom_detail_id`) REFERENCES `erp_bom_details`(`id`) ON DELETE CASCADE
);

-- Create `erp_pwo_bom_mapping_history` table
CREATE TABLE `erp_pwo_bom_mapping_history` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `source_id` BIGINT UNSIGNED NULL,
    `pwo_id` BIGINT UNSIGNED NULL,
    `pwo_mapping_id` BIGINT UNSIGNED NULL,
    `bom_id` BIGINT UNSIGNED NULL,
    `bom_detail_id` BIGINT UNSIGNED NULL,
    `item_id` BIGINT UNSIGNED NULL,
    `item_code` VARCHAR(255) NULL,
    `attributes` JSON NULL,
    `uom_id` BIGINT UNSIGNED NULL,
    `qty` DOUBLE(20, 6) DEFAULT 0,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    -- Foreign Keys
    FOREIGN KEY (`pwo_id`) REFERENCES `erp_mfg_orders_history`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`bom_id`) REFERENCES `erp_boms_history`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`bom_detail_id`) REFERENCES `erp_bom_details_history`(`id`) ON DELETE CASCADE
);

-- All Databases--
-- time 12:08 --
CREATE TABLE `erp_mo_item_locations` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `mo_id` BIGINT UNSIGNED NULL,
    `mo_item_id` BIGINT UNSIGNED NULL,
    `item_id` BIGINT UNSIGNED NULL,
    `item_code` VARCHAR(255) NULL,
    `store_id` BIGINT UNSIGNED NULL,
    `store_code` VARCHAR(255) NULL,
    `rack_id` BIGINT UNSIGNED NULL,
    `rack_code` VARCHAR(255) NULL,
    `shelf_id` BIGINT UNSIGNED NULL,
    `shelf_code` VARCHAR(255) NULL,
    `bin_id` BIGINT UNSIGNED NULL,
    `bin_code` VARCHAR(255) NULL,
    `quantity` DOUBLE(20,6) DEFAULT 0.000000,
    `inventory_uom_qty` DOUBLE(20,6) DEFAULT 0.000000,
    `created_by` BIGINT UNSIGNED NULL,
    `updated_by` BIGINT UNSIGNED NULL,
    `deleted_by` BIGINT UNSIGNED NULL,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

###############2025_03_03_083400_add_column_bom_type_to_erp_items_table##########################
-- All Databases--
-- Time 2:27 PM ---
alter table `erp_boms` add `customizable` enum('yes', 'no') not null default 'no' after `type`, add `bom_type` enum('fixed', 'dynamic') not null default 'fixed' after `customizable`;
alter table `erp_boms_history` add `customizable` enum('yes', 'no') not null default 'no' after `type`, add `bom_type` enum('fixed', 'dynamic') not null default 'fixed' after `type`;

