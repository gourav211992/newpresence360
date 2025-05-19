--all databases--
CREATE TABLE `erp_so_item_bom` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `sale_order_id` BIGINT UNSIGNED NOT NULL,
    `so_item_id` BIGINT UNSIGNED NOT NULL,
    `bom_id` BIGINT UNSIGNED NOT NULL,
    `bom_detail_id` BIGINT UNSIGNED NOT NULL,
    `uom_id` BIGINT UNSIGNED NOT NULL,
    `item_id` BIGINT UNSIGNED NOT NULL,
    `item_code` VARCHAR(255) NOT NULL,
    `item_attributes` JSON NULL,
    `qty` DOUBLE(20,6) NOT NULL,
    `station_id` BIGINT UNSIGNED NOT NULL,
    `station_name` VARCHAR(255) NOT NULL,
    `created_by` BIGINT UNSIGNED NULL,
    `updated_by` BIGINT UNSIGNED NULL,
    `deleted_by` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    INDEX (`order_column`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `erp_so_item_bom_history` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `source_id` BIGINT UNSIGNED NOT NULL,
    `sale_order_id` BIGINT UNSIGNED NOT NULL,
    `so_item_id` BIGINT UNSIGNED NOT NULL,
    `bom_id` BIGINT UNSIGNED NOT NULL,
    `bom_detail_id` BIGINT UNSIGNED NOT NULL,
    `uom_id` BIGINT UNSIGNED NOT NULL,
    `item_id` BIGINT UNSIGNED NOT NULL,
    `item_code` VARCHAR(255) NOT NULL,
    `item_attributes` JSON NULL,
    `qty` DOUBLE(20,6) NOT NULL,
    `station_id` BIGINT UNSIGNED NOT NULL,
    `station_name` VARCHAR(255) NOT NULL,
    `created_by` BIGINT UNSIGNED NULL,
    `updated_by` BIGINT UNSIGNED NULL,
    `deleted_by` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

alter table `erp_pslip_item_details` add `dn_item_id` bigint unsigned null after `pslip_item_id`  
alter table `erp_pslip_item_details_history` add `dn_item_id` bigint unsigned null after `pslip_item_id`

######################################################################################################################################
--Master Database--
UPDATE erp_services SET name = "Packing Slip" where alias = "pslip";