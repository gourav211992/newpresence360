ALTER TABLE erp_categories
ADD COLUMN hsn_id BIGINT UNSIGNED NULL,
ADD CONSTRAINT fk_hsn_id
FOREIGN KEY (hsn_id) REFERENCES erp_hsns(id) ON DELETE SET NULL;