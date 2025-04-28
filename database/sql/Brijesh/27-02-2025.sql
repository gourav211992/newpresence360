ALTER TABLE `erp_pb_headers` DROP FOREIGN KEY `erp_pb_headers_deleted_by_foreign`;
ALTER TABLE `erp_pb_headers` DROP INDEX `erp_pb_headers_deleted_by_foreign`;
ALTER TABLE `erp_pb_headers` DROP FOREIGN KEY `erp_pb_headers_updated_by_foreign`;
ALTER TABLE `erp_pb_headers` DROP INDEX `erp_pb_headers_updated_by_foreign`;
ALTER TABLE `erp_pb_headers` DROP FOREIGN KEY `erp_pb_headers_created_by_foreign`;
ALTER TABLE `erp_pb_headers` DROP INDEX `erp_pb_headers_created_by_foreign`;

ALTER TABLE erp_pb_details 
ADD COLUMN order_qty DECIMAL(15,6) DEFAULT 0.00 NULL AFTER cost_center_name,
ADD COLUMN rejected_qty DECIMAL(15,6) DEFAULT 0.00 NULL AFTER accepted_qty,
ADD COLUMN po_rate DECIMAL(15,6) DEFAULT 0.00 NULL AFTER inventory_uom_qty,
ADD COLUMN item_variance DECIMAL(15,6) DEFAULT 0.00 NULL AFTER rate;

ALTER TABLE `erp_pb_header_histories` DROP FOREIGN KEY `erp_pb_header_histories_deleted_by_foreign`;
ALTER TABLE `erp_pb_header_histories` DROP INDEX `erp_pb_header_histories_deleted_by_foreign`;
ALTER TABLE `erp_pb_header_histories` DROP FOREIGN KEY `erp_pb_header_histories_updated_by_foreign`;
ALTER TABLE `erp_pb_header_histories` DROP INDEX `erp_pb_header_histories_updated_by_foreign`;
ALTER TABLE `erp_pb_header_histories` DROP FOREIGN KEY `erp_pb_header_histories_created_by_foreign`;
ALTER TABLE `erp_pb_header_histories` DROP INDEX `erp_pb_header_histories_created_by_foreign`;

ALTER TABLE erp_pb_detail_histories 
ADD COLUMN order_qty DECIMAL(15,6) DEFAULT 0.00 NULL AFTER cost_center_name,
ADD COLUMN rejected_qty DECIMAL(15,6) DEFAULT 0.00 NULL AFTER accepted_qty,
ADD COLUMN po_rate DECIMAL(15,6) DEFAULT 0.00 NULL AFTER inventory_uom_qty,
ADD COLUMN item_variance DECIMAL(15,6) DEFAULT 0.00 NULL AFTER rate;