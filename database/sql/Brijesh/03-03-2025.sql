-- Add Columns in erp_pr_details table
ALTER TABLE
    `erp_pr_details`
ADD
    `item_id` bigint(20) NULL
AFTER
    `consumption`,
ADD
    `qa` ENUM('yes', 'no') DEFAULT 'no'
AFTER
    `item_id`;

-- Create the stock_ledger_reservations table
CREATE TABLE stock_ledger_reservations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    stock_ledger_id BIGINT UNSIGNED NULL,
    order_id BIGINT UNSIGNED NULL,
    quantity DECIMAL(15, 2) NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    -- Adjust ENUM values based on ConstantHelper::STATUS
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    INDEX idx_status (status)
);

-- Alter the stock_ledger table to add reserved_qty column
ALTER TABLE
    stock_ledger
ADD
    COLUMN reserved_qty DECIMAL(15, 2) DEFAULT 0.00
AFTER
    issue_qty;

ALTER TABLE
    erp_mrn_details
ADD
    COLUMN gate_entry_detail_id BIGINT NULL
AFTER
    purchase_order_item_id;

ALTER TABLE
    erp_mrn_detail_histories
ADD
    COLUMN gate_entry_detail_id BIGINT NULL
AFTER
    purchase_order_item_id;
