ALTER TABLE erp_legals 
ADD COLUMN document_status VARCHAR(50) NOT NULL DEFAULT 'pending',
ADD COLUMN approval_level INT NOT NULL DEFAULT 1;