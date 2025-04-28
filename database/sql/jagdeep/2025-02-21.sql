--all database--
CREATE TABLE erp_material_issue_header (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id BIGINT UNSIGNED NULL,
    group_id BIGINT UNSIGNED NULL,
    company_id BIGINT UNSIGNED NULL,
    book_id BIGINT UNSIGNED NULL,
    book_code VARCHAR(255) NULL,
    document_number VARCHAR(255) NULL,
    doc_number_type ENUM('manual', 'auto') DEFAULT 'manual',
    doc_reset_pattern ENUM('daily', 'monthly', 'yearly') NULL DEFAULT NULL,
    doc_prefix VARCHAR(255) NULL,
    doc_suffix VARCHAR(255) NULL,
    doc_no INT NULL,
    document_date DATE NULL,
    revision_number VARCHAR(255) DEFAULT '0',
    revision_date DATE NULL,
    reference_number VARCHAR(255) NULL,
    department_id BIGINT UNSIGNED NOT NULL,
    department_code BIGINT UNSIGNED NOT NULL,
    from_store_id BIGINT UNSIGNED NOT NULL,
    from_store_code VARCHAR(255) NOT NULL,
    to_store_id BIGINT UNSIGNED NULL,
    to_store_code VARCHAR(255) NULL,
    vendor_id BIGINT UNSIGNED NULL,
    vendor_code VARCHAR(255) NULL,
    consignee_name VARCHAR(255) NULL,
    consignment_no VARCHAR(255) NULL,
    eway_bill_no VARCHAR(255) NULL,
    transporter_name VARCHAR(255) NULL,
    vehicle_no VARCHAR(255) NULL,
    billing_address BIGINT UNSIGNED NULL,
    shipping_address BIGINT UNSIGNED NULL,
    currency_id BIGINT UNSIGNED NULL,
    currency_code VARCHAR(255) NULL,
    document_status VARCHAR(255) NULL,
    approval_level INT DEFAULT 1 COMMENT 'current approval level',
    remarks TEXT NULL,
    total_item_value DECIMAL(15,2) DEFAULT 0.00,
    total_discount_value DECIMAL(15,2) DEFAULT 0.00,
    total_tax_value DECIMAL(15,2) DEFAULT 0.00,
    total_expense_value DECIMAL(15,2) DEFAULT 0.00,
    total_amount DECIMAL(15,2) DEFAULT 0.00,
    created_by BIGINT UNSIGNED NULL,
    updated_by BIGINT UNSIGNED NULL,
    deleted_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL
);

CREATE TABLE erp_mi_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    material_issue_id BIGINT UNSIGNED NULL,
    mi_item_id BIGINT UNSIGNED NULL COMMENT 'erp_mi_item_id',
    item_id BIGINT UNSIGNED NULL,
    item_code VARCHAR(255) NULL,
    item_name VARCHAR(255) NULL,
    hsn_id BIGINT UNSIGNED NULL,
    hsn_code VARCHAR(255) NULL,
    uom_id BIGINT UNSIGNED NULL,
    uom_code VARCHAR(255) NULL,
    issue_qty DECIMAL(15,2) DEFAULT 0.00,
    inventory_uom_id BIGINT UNSIGNED NULL,
    inventory_uom_code VARCHAR(255) NULL,
    inventory_uom_qty DECIMAL(15,2) DEFAULT 0.00,
    rate DECIMAL(15,2) DEFAULT 0.00,
    item_discount_amount DECIMAL(15,2) DEFAULT 0.00,
    total_item_amount DECIMAL(15,2) DEFAULT 0.00,
    remarks TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (material_issue_id) REFERENCES erp_material_issue_header(id) ON DELETE CASCADE
);

CREATE TABLE erp_mi_item_attributes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    material_issue_id BIGINT UNSIGNED NULL,
    mi_item_id BIGINT UNSIGNED NULL,
    item_attribute_id BIGINT UNSIGNED NULL COMMENT 'use tbl erp_item_attributes',
    item_code VARCHAR(255) NULL,
    attribute_name VARCHAR(255) NULL,
    attr_name BIGINT UNSIGNED NULL,
    attribute_value VARCHAR(255) NULL,
    attr_value BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (mi_item_id) REFERENCES erp_mi_items(id) ON DELETE CASCADE,
    FOREIGN KEY (material_issue_id) REFERENCES erp_material_issue_header(id) ON DELETE CASCADE
);

CREATE TABLE erp_mi_item_locations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    material_issue_id BIGINT UNSIGNED NULL,
    mi_item_id BIGINT UNSIGNED NULL,
    item_id BIGINT UNSIGNED NULL,
    item_code VARCHAR(255) NULL,
    store_id BIGINT UNSIGNED NULL,
    store_code VARCHAR(255) NULL,
    rack_id BIGINT UNSIGNED NULL,
    rack_code VARCHAR(255) NULL,
    shelf_id BIGINT UNSIGNED NULL,
    shelf_code VARCHAR(255) NULL,
    bin_id BIGINT UNSIGNED NULL,
    bin_code VARCHAR(255) NULL,
    type ENUM('from', 'to'),
    quantity DOUBLE(15,2) DEFAULT 0.00,
    inventory_uom_qty DOUBLE(15,2) DEFAULT 0.00,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (mi_item_id) REFERENCES erp_mi_items(id) ON DELETE CASCADE,
    FOREIGN KEY (material_issue_id) REFERENCES erp_material_issue_header(id) ON DELETE CASCADE
);

CREATE TABLE erp_material_issue_ted (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    material_issue_id BIGINT UNSIGNED NULL,
    mi_item_id BIGINT UNSIGNED NULL,
    ted_type ENUM('Tax', 'Expense', 'Discount') COMMENT 'Tax, Expense, Discount',
    ted_level ENUM('H', 'D') COMMENT 'H or D',
    ted_id BIGINT UNSIGNED NULL,
    ted_group_code VARCHAR(255) NULL,
    ted_name VARCHAR(255) NULL,
    assessment_amount DECIMAL(15,2) DEFAULT 0.00,
    ted_percentage DECIMAL(15,2) DEFAULT 0.00 COMMENT 'TED Percentage',
    ted_amount DECIMAL(15,2) DEFAULT 0.00 COMMENT 'TED Amount',
    applicable_type VARCHAR(255) NULL COMMENT 'Deduction, Collection',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (material_issue_id) REFERENCES erp_material_issue_header(id) ON DELETE CASCADE,
    FOREIGN KEY (mi_item_id) REFERENCES erp_mi_items(id) ON DELETE CASCADE
);


CREATE TABLE erp_material_issue_header_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    source_id BIGINT UNSIGNED NULL,
    organization_id BIGINT UNSIGNED NULL,
    group_id BIGINT UNSIGNED NULL,
    company_id BIGINT UNSIGNED NULL,
    book_id BIGINT UNSIGNED NULL,
    book_code VARCHAR(255) NULL,
    document_number VARCHAR(255) NULL,
    doc_number_type ENUM('AUTO', 'MANUAL') DEFAULT 'MANUAL',
    doc_reset_pattern ENUM('DAILY', 'MONTHLY', 'YEARLY') NULL,
    doc_prefix VARCHAR(255) NULL,
    doc_suffix VARCHAR(255) NULL,
    doc_no INT NULL,
    document_date DATE NULL,
    revision_number VARCHAR(255) DEFAULT '0',
    revision_date DATE NULL,
    reference_number VARCHAR(255) NULL,
    department_id BIGINT UNSIGNED NOT NULL,
    department_code BIGINT UNSIGNED NOT NULL,
    from_store_id BIGINT UNSIGNED NOT NULL,
    from_store_code VARCHAR(255) NOT NULL,
    to_store_id BIGINT UNSIGNED NULL,
    to_store_code VARCHAR(255) NULL,
    vendor_id BIGINT UNSIGNED NULL,
    vendor_code VARCHAR(255) NULL,
    consignee_name VARCHAR(255) NULL,
    consignment_no VARCHAR(255) NULL,
    eway_bill_no VARCHAR(255) NULL,
    transporter_name VARCHAR(255) NULL,
    vehicle_no VARCHAR(255) NULL,
    billing_address BIGINT UNSIGNED NULL,
    shipping_address BIGINT UNSIGNED NULL,
    currency_id BIGINT UNSIGNED NULL,
    currency_code VARCHAR(255) NULL,
    document_status VARCHAR(255) NULL,
    approval_level INT DEFAULT 1,
    remarks TEXT NULL,
    total_item_value DECIMAL(15,2) DEFAULT 0.00,
    total_discount_value DECIMAL(15,2) DEFAULT 0.00,
    total_tax_value DECIMAL(15,2) DEFAULT 0.00,
    total_expense_value DECIMAL(15,2) DEFAULT 0.00,
    total_amount DECIMAL(15,2) DEFAULT 0.00,
    org_currency_id BIGINT UNSIGNED NULL,
    org_currency_code VARCHAR(255) NULL,
    org_currency_exg_rate DECIMAL(15,6) NULL,
    comp_currency_id BIGINT UNSIGNED NULL,
    comp_currency_code VARCHAR(255) NULL,
    comp_currency_exg_rate DECIMAL(15,6) NULL,
    group_currency_id BIGINT UNSIGNED NULL,
    group_currency_code VARCHAR(255) NULL,
    group_currency_exg_rate DECIMAL(15,6) NULL,
    created_by BIGINT UNSIGNED NULL,
    updated_by BIGINT UNSIGNED NULL,
    deleted_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL
);

CREATE TABLE erp_mi_items_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    source_id BIGINT UNSIGNED NULL,
    material_issue_id BIGINT UNSIGNED NULL,
    mi_item_id BIGINT UNSIGNED NULL,
    item_id BIGINT UNSIGNED NULL,
    item_code VARCHAR(255) NULL,
    item_name VARCHAR(255) NULL,
    hsn_id BIGINT UNSIGNED NULL,
    hsn_code VARCHAR(255) NULL,
    uom_id BIGINT UNSIGNED NULL,
    uom_code VARCHAR(255) NULL,
    issue_qty DECIMAL(15,2) DEFAULT 0.00,
    inventory_uom_id BIGINT UNSIGNED NULL,
    inventory_uom_code VARCHAR(255) NULL,
    inventory_uom_qty DECIMAL(15,2) DEFAULT 0.00,
    rate DECIMAL(15,2) DEFAULT 0.00,
    item_discount_amount DECIMAL(15,2) DEFAULT 0.00,
    header_discount_amount DECIMAL(15,2) DEFAULT 0.00,
    tax_amount DECIMAL(15,2) DEFAULT 0.00,
    item_expense_amount DECIMAL(15,2) DEFAULT 0.00,
    header_expense_amount DECIMAL(15,2) DEFAULT 0.00,
    total_item_amount DECIMAL(15,2) DEFAULT 0.00,
    remarks TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (material_issue_id) REFERENCES erp_material_issue_header_history(id) ON DELETE CASCADE
);

CREATE TABLE erp_mi_item_attributes_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    source_id BIGINT UNSIGNED NULL,
    material_issue_id BIGINT UNSIGNED NULL,
    mi_item_id BIGINT UNSIGNED NULL,
    item_attribute_id BIGINT UNSIGNED NULL,
    item_code VARCHAR(255) NULL,
    attribute_name VARCHAR(255) NULL,
    attr_name BIGINT UNSIGNED NULL,
    attribute_value VARCHAR(255) NULL,
    attr_value BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (mi_item_id) REFERENCES erp_mi_items_history(id) ON DELETE CASCADE,
    FOREIGN KEY (material_issue_id) REFERENCES erp_material_issue_header_history(id) ON DELETE CASCADE
);

CREATE TABLE erp_mi_item_locations_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    source_id BIGINT UNSIGNED NULL,
    material_issue_id BIGINT UNSIGNED NULL,
    mi_item_id BIGINT UNSIGNED NULL,
    item_id BIGINT UNSIGNED NULL,
    item_code VARCHAR(255) NULL,
    store_id BIGINT UNSIGNED NULL,
    store_code VARCHAR(255) NULL,
    rack_id BIGINT UNSIGNED NULL,
    rack_code VARCHAR(255) NULL,
    shelf_id BIGINT UNSIGNED NULL,
    shelf_code VARCHAR(255) NULL,
    bin_id BIGINT UNSIGNED NULL,
    bin_code VARCHAR(255) NULL,
    type ENUM('from', 'to'),
    quantity DOUBLE(15,2) DEFAULT 0.00,
    inventory_uom_qty DOUBLE(15,2) DEFAULT 0.00,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (mi_item_id) REFERENCES erp_mi_items_history(id) ON DELETE CASCADE,
    FOREIGN KEY (material_issue_id) REFERENCES erp_material_issue_header_history(id) ON DELETE CASCADE
);

CREATE TABLE erp_material_issue_ted_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    source_id BIGINT UNSIGNED NULL,
    material_issue_id BIGINT UNSIGNED NULL,
    mi_item_id BIGINT UNSIGNED NULL,
    ted_type ENUM('Tax', 'Expense', 'Discount'),
    ted_level ENUM('H', 'D'),
    ted_id BIGINT UNSIGNED NULL,
    ted_group_code VARCHAR(255) NULL,
    ted_name VARCHAR(255) NULL,
    assessment_amount DECIMAL(15,2) DEFAULT 0.00,
    ted_percentage DECIMAL(15,2) DEFAULT 0.00,
    ted_amount DECIMAL(15,2) DEFAULT 0.00,
    applicable_type VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (material_issue_id) REFERENCES erp_material_issue_header_history(id) ON DELETE CASCADE,
    FOREIGN KEY (mi_item_id) REFERENCES erp_mi_items_history(id) ON DELETE CASCADE
);

ALTER TABLE `erp_mi_items` 
ADD COLUMN `from_store_id` BIGINT UNSIGNED NULL AFTER `uom_code`,
ADD COLUMN `from_store_code` VARCHAR(255) NULL AFTER `from_store_id`,
ADD COLUMN `to_store_id` BIGINT UNSIGNED NULL AFTER `from_store_code`,
ADD COLUMN `to_store_code` VARCHAR(255) NULL AFTER `to_store_id`;

ALTER TABLE `erp_mi_items_history` 
ADD COLUMN `from_store_id` BIGINT UNSIGNED NULL AFTER `uom_code`,
ADD COLUMN `from_store_code` VARCHAR(255) NULL AFTER `from_store_id`,
ADD COLUMN `to_store_id` BIGINT UNSIGNED NULL AFTER `from_store_code`,
ADD COLUMN `to_store_code` VARCHAR(255) NULL AFTER `to_store_id`;

ALTER TABLE erp_mo_items ADD mi_qty DOUBLE(20,6) NOT NULL DEFAULT '0' AFTER qty;
ALTER TABLE erp_mo_items_history ADD mi_qty DOUBLE(20,6) NOT NULL DEFAULT '0' AFTER qty;

-- Production Slips and Production Slips History Tables
CREATE TABLE erp_production_slips (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id BIGINT UNSIGNED NULL,
    group_id BIGINT UNSIGNED NULL,
    company_id BIGINT UNSIGNED NULL,
    store_id BIGINT UNSIGNED NULL,
    book_id BIGINT UNSIGNED NULL COMMENT 'books tbl id',
    book_code VARCHAR(255) NULL,
    document_number VARCHAR(255) NULL,
    document_date DATE NULL,
    doc_number_type ENUM('AUTO', 'MANUAL') DEFAULT 'MANUAL',
    doc_reset_pattern ENUM('DAILY', 'MONTHLY', 'YEARLY') NULL,
    doc_prefix VARCHAR(255) NULL,
    doc_suffix VARCHAR(255) NULL,
    doc_no INT NULL,
    document_status VARCHAR(255) NULL,
    revision_number INT DEFAULT 0,
    revision_date DATE NULL,
    approval_level INT DEFAULT 1 COMMENT 'Current Approval Level',
    remarks TEXT NULL,
    total_item_value DECIMAL(15,2) DEFAULT 0.00,
    total_discount_value DECIMAL(15,2) DEFAULT 0.00,
    total_tax_value DECIMAL(15,2) DEFAULT 0.00,
    total_expense_value DECIMAL(15,2) DEFAULT 0.00,
    total_amount DECIMAL(15,2) DEFAULT 0.00,
    org_currency_id BIGINT UNSIGNED NULL,
    org_currency_code VARCHAR(255) NULL,
    org_currency_exg_rate DECIMAL(15,6) NULL,
    comp_currency_id BIGINT UNSIGNED NULL,
    comp_currency_code VARCHAR(255) NULL,
    comp_currency_exg_rate DECIMAL(15,6) NULL,
    group_currency_id BIGINT UNSIGNED NULL,
    group_currency_code VARCHAR(255) NULL,
    group_currency_exg_rate DECIMAL(15,6) NULL,
    created_by BIGINT UNSIGNED NULL,
    updated_by BIGINT UNSIGNED NULL,
    deleted_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (store_id) REFERENCES erp_stores(id) ON DELETE CASCADE
);

CREATE TABLE erp_production_slips_history LIKE erp_production_slips;
ALTER TABLE erp_production_slips_history ADD COLUMN source_id BIGINT UNSIGNED NULL;

-- Production Slip Items and Production Slip Items History Tables
CREATE TABLE erp_pslip_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pslip_id BIGINT UNSIGNED NULL,
    production_bom_id BIGINT UNSIGNED NULL COMMENT 'erp_boms id',
    item_id BIGINT UNSIGNED NULL,
    so_item_id BIGINT UNSIGNED NULL,
    item_code VARCHAR(255) NULL,
    item_name VARCHAR(255) NULL,
    hsn_id BIGINT UNSIGNED NULL,
    hsn_code VARCHAR(255) NULL,
    uom_id BIGINT UNSIGNED NULL,
    uom_code VARCHAR(255) NULL,
    store_id BIGINT UNSIGNED NOT NULL,
    qty DECIMAL(15,2) DEFAULT 0.00,
    inventory_uom_id BIGINT UNSIGNED NULL,
    inventory_uom_code VARCHAR(255) NULL,
    inventory_uom_qty DECIMAL(15,2) DEFAULT 0.00,
    rate DECIMAL(15,2) DEFAULT 0.00,
    item_discount_amount DECIMAL(15,2) DEFAULT 0.00,
    header_discount_amount DECIMAL(15,2) DEFAULT 0.00,
    tax_amount DECIMAL(15,2) DEFAULT 0.00,
    item_expense_amount DECIMAL(15,2) DEFAULT 0.00,
    header_expense_amount DECIMAL(15,2) DEFAULT 0.00,
    total_item_amount DECIMAL(15,2) DEFAULT 0.00,
    customer_id BIGINT UNSIGNED NULL,
    order_id BIGINT UNSIGNED NULL,
    remarks TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pslip_id) REFERENCES erp_production_slips(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES erp_customers(id) ON DELETE CASCADE
);

CREATE TABLE erp_pslip_items_history LIKE erp_pslip_items;
ALTER TABLE erp_pslip_items_history ADD COLUMN source_id BIGINT UNSIGNED NULL;
ALTER TABLE erp_pslip_items_history DROP FOREIGN KEY erp_pslip_items_ibfk_1;
ALTER TABLE erp_pslip_items_history ADD CONSTRAINT FOREIGN KEY (pslip_id) REFERENCES erp_production_slips_history(id) ON DELETE CASCADE;

-- Production Slip Item Attributes and History Tables
CREATE TABLE erp_pslip_item_attributes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pslip_id BIGINT UNSIGNED NULL,
    pslip_item_id BIGINT UNSIGNED NULL,
    item_attribute_id BIGINT UNSIGNED NULL COMMENT 'use tbl erp_item_attributes',
    item_code VARCHAR(255) NULL,
    attribute_name VARCHAR(255) NULL,
    attr_name BIGINT UNSIGNED NULL,
    attribute_value VARCHAR(255) NULL,
    attr_value BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pslip_id) REFERENCES erp_production_slips(id) ON DELETE CASCADE,
    FOREIGN KEY (pslip_item_id) REFERENCES erp_pslip_items(id) ON DELETE CASCADE
);

CREATE TABLE erp_pslip_item_attributes_history LIKE erp_pslip_item_attributes;
ALTER TABLE erp_pslip_item_attributes_history ADD COLUMN source_id BIGINT UNSIGNED NULL;
ALTER TABLE erp_pslip_item_attributes_history DROP FOREIGN KEY erp_pslip_item_attributes_ibfk_1;
ALTER TABLE erp_pslip_item_attributes_history ADD CONSTRAINT FOREIGN KEY (pslip_id) REFERENCES erp_production_slips_history(id) ON DELETE CASCADE;

-- Production Slip Item Locations and History Tables
CREATE TABLE erp_pslip_item_locations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pslip_id BIGINT UNSIGNED NULL,
    pslip_item_id BIGINT UNSIGNED NULL,
    item_id BIGINT UNSIGNED NULL,
    item_code VARCHAR(255) NULL,
    store_id BIGINT UNSIGNED NULL,
    store_code VARCHAR(255) NULL,
    rack_id BIGINT UNSIGNED NULL,
    rack_code VARCHAR(255) NULL,
    shelf_id BIGINT UNSIGNED NULL,
    shelf_code VARCHAR(255) NULL,
    bin_id BIGINT UNSIGNED NULL,
    bin_code VARCHAR(255) NULL,
    quantity DECIMAL(15,2) DEFAULT 0.00,
    inventory_uom_qty DECIMAL(15,2) DEFAULT 0.00,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (store_id) REFERENCES erp_stores(id) ON DELETE CASCADE,
    FOREIGN KEY (rack_id) REFERENCES erp_racks(id) ON DELETE CASCADE,
    FOREIGN KEY (shelf_id) REFERENCES erp_shelfs(id) ON DELETE CASCADE,
    FOREIGN KEY (bin_id) REFERENCES erp_bins(id) ON DELETE CASCADE
);

CREATE TABLE erp_pslip_item_locations_history LIKE erp_pslip_item_locations;
ALTER TABLE erp_pslip_item_locations_history ADD COLUMN source_id BIGINT UNSIGNED NULL;
ALTER TABLE erp_pslip_item_locations_history DROP FOREIGN KEY erp_pslip_item_locations_ibfk_1;
ALTER TABLE erp_pslip_item_locations_history ADD CONSTRAINT FOREIGN KEY (pslip_id) REFERENCES erp_production_slips_history(id) ON DELETE CASCADE;


##################################################################################################################################################################


