ALTER TABLE
    `erp_po_items`
ADD
    `ge_qty` DOUBLE(20, 6) NULL DEFAULT '0.000000'
AFTER
    `order_qty`;