CREATE TABLE erp_loan_appraisal_credit_scoring (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    loan_id BIGINT UNSIGNED NOT NULL,
    loan_appraisal_id BIGINT UNSIGNED NOT NULL,
    loan_type VARCHAR(255) NOT NULL,
    credit_data JSON DEFAULT NULL,
    document_completeness JSON DEFAULT NULL,
    basic_eligibility JSON DEFAULT NULL,
    collateral_credit_history JSON DEFAULT NULL,
    remarks LONGTEXT DEFAULT NULL,
    financial_analysis JSON DEFAULT NULL,
    collateral_1 JSON DEFAULT NULL,
    collateral_2 JSON DEFAULT NULL,
    compliance_and_risk JSON DEFAULT NULL,
    community JSON DEFAULT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);