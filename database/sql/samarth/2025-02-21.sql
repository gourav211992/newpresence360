-- For Master Database 
INSERT INTO erp_services (name, alias) VALUES ('Transporter Request', 'tr');
########################################################################################################################################


--For all Databases
-- Creating erp_transporter_requests table
CREATE TABLE erp_transporter_requests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organization_id BIGINT UNSIGNED NULL,
    group_id BIGINT UNSIGNED NULL,
    company_id BIGINT UNSIGNED NULL,
    book_id BIGINT UNSIGNED NULL,
    book_code VARCHAR(255) NULL,
    document_number VARCHAR(255) NULL,
    doc_number_type ENUM('Auto', 'Manually') DEFAULT 'Manually',
    doc_reset_pattern ENUM('Never', 'Yearly', 'Quarterly', 'Monthly') NULL,
    doc_prefix VARCHAR(255) NULL,
    doc_suffix VARCHAR(255) NULL,
    document_date DATE NULL,
    doc_no BIGINT UNSIGNED NULL,
    loading_date_time DATETIME NULL,
    revision_number VARCHAR(255) DEFAULT '0',
    revision_date DATE NULL,
    approval_level INT DEFAULT 1 COMMENT 'current approval level',
    document_status VARCHAR(255) NULL COMMENT 'completed,shortlisted,closed',
    vehicle_type VARCHAR(255) NULL,
    total_weight DECIMAL(15,2) NULL,
    uom_id BIGINT UNSIGNED NULL,
    uom_code VARCHAR(255) NULL,
    bid_start DATETIME NULL,
    bid_end DATETIME NULL,
    transporter_ids JSON NULL,
    remarks TEXT NULL,
    selected_bid_id BIGINT UNSIGNED NULL,
    created_by BIGINT UNSIGNED NULL,
    updated_by BIGINT UNSIGNED NULL,
    deleted_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    attachment JSON NULL,
    CONSTRAINT fk_erp_transporter_requests_book FOREIGN KEY (book_id) REFERENCES erp_books(id) ON DELETE SET NULL
);

-- Creating erp_transporter_request_locations table
CREATE TABLE erp_transporter_request_locations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    transporter_request_id BIGINT UNSIGNED NULL,
    address_id BIGINT UNSIGNED NULL,
    location_id BIGINT UNSIGNED NULL,
    location_name VARCHAR(255) NULL,
    location_type ENUM('pick_up', 'drop_off') NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    CONSTRAINT fk_transporter_request FOREIGN KEY (transporter_request_id) REFERENCES erp_transporter_requests(id) ON DELETE CASCADE,
    CONSTRAINT fk_location FOREIGN KEY (location_id) REFERENCES erp_stores(id) ON DELETE SET NULL,
    CONSTRAINT fk_address FOREIGN KEY (address_id) REFERENCES erp_addresses(id) ON DELETE SET NULL
);

-- Creating erp_transporter_request_bids table
CREATE TABLE erp_transporter_request_bids (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    transporter_request_id BIGINT UNSIGNED NOT NULL COMMENT 'id of erp_transporter_requests',
    transporter_id BIGINT UNSIGNED NULL,
    bid_price DECIMAL(15,2) NULL,
    vehicle_number VARCHAR(255) NULL,
    driver_name VARCHAR(255) NULL,
    driver_contact_no VARCHAR(255) NULL,
    transporter_remarks TEXT NULL,
    bid_status ENUM('submitted', 'shortlisted', 'confirmed', 'cancelled') NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    CONSTRAINT fk_transporter_request_bid FOREIGN KEY (transporter_request_id) REFERENCES erp_transporter_requests(id) ON DELETE CASCADE,
    CONSTRAINT fk_transporter FOREIGN KEY (transporter_id) REFERENCES erp_vendors(id) ON DELETE SET NULL
);

-- Creating erp_vehicle_types table
CREATE TABLE erp_vehicle_types (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    vehicle_type VARCHAR(255) NULL,
    capacity DECIMAL(15,6) NULL,
    uom_id BIGINT UNSIGNED NULL,
    status ENUM('active', 'inactive') DEFAULT 'active'
);

INSERT INTO erp_vehicle_types (id, vehicle_type, capacity, uom_id, status) 
VALUES (1, 'Truck', 1000.000000, 6, 'active');

INSERT INTO erp_vehicle_types (id, vehicle_type, capacity, uom_id, status) 
VALUES (2, 'Tampu', 500.000000, 6, 'active');

