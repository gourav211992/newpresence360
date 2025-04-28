
CREATE TABLE erp_wip_accounts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    group_id BIGINT NULL,
    company_id INT NULL,
    organization_id INT UNSIGNED NULL,
    book_id JSON NULL,
    ledger_id BIGINT UNSIGNED NULL,
    ledger_group_id BIGINT UNSIGNED NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (ledger_id) REFERENCES erp_ledgers(id) ON DELETE CASCADE,
    FOREIGN KEY (ledger_group_id) REFERENCES erp_groups(id) ON DELETE CASCADE
);

ALTER TABLE `upload_item_masters `
ADD COLUMN `item_code_type` VARCHAR(100) NULL;

ALTER TABLE `upload_item_masters `
ADD COLUMN `cost_price` DECIMAL(10,2) NULL,
ADD COLUMN `sell_price` DECIMAL(10,2) NULL,

ALTER TABLE erp_items
ADD COLUMN storage_type VARCHAR(255) NULL AFTER service_type;

ALTER TABLE `erp_notes`
ADD COLUMN `created_by_type` VARCHAR(100) NULL;





