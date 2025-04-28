CREATE OR REPLACE VIEW erp_transactions AS
SELECT * FROM
(
    SELECT 
        id AS document_id, 
        group_id, 
        company_id, 
        organization_id, 
        'purchase-indent' AS document_type, 
        book_id, 
        book_code COLLATE utf8mb4_general_ci as book_code, 
        document_number COLLATE utf8mb4_general_ci  AS document_number, 
        document_date, 
        revision_number, 
        revision_date, 
        (CASE WHEN requester_type = 'user' THEN user_id ELSE department_id END) AS party_id, 
        NULL AS party_code, 
        requester_type AS party_type, 
        NUll AS currency_id, 
        NUll AS currency_code, 
        document_status COLLATE utf8mb4_general_ci  AS document_status, 
        approval_level,
        0 AS total_amount,
        created_by
    FROM erp_purchase_indents
    UNION ALL
    SELECT 
        id AS document_id, 
        group_id, 
        company_id, 
        organization_id, 
        'po' AS document_type, 
        book_id, 
        book_code COLLATE utf8mb4_general_ci as book_code, 
        document_number COLLATE utf8mb4_general_ci  AS document_number, 
        document_date, 
        revision_number, 
        revision_date, 
        vendor_id AS party_id, 
        vendor_code COLLATE utf8mb4_general_ci AS party_code, 
        'vendor' AS party_type, 
        currency_id, 
        currency_code, 
        document_status COLLATE utf8mb4_general_ci  AS document_status, 
        approval_level,
        (total_item_value - total_discount_value + total_tax_value + total_expense_value) AS total_amount,
        created_by
    FROM erp_purchase_orders WHERE type = 'po'
    UNION ALL
    SELECT 
        id AS document_id, 
        group_id, 
        company_id, 
        organization_id, 
        'job-order' AS document_type, 
        book_id, 
        book_code COLLATE utf8mb4_general_ci as book_code, 
        document_number COLLATE utf8mb4_general_ci  AS document_number, 
        document_date, 
        revision_number, 
        revision_date, 
        vendor_id AS party_id, 
        vendor_code COLLATE utf8mb4_general_ci AS party_code, 
        'vendor' AS party_type, 
        currency_id, 
        currency_code, 
        document_status COLLATE utf8mb4_general_ci  AS document_status, 
        approval_level,
        (total_item_value - total_discount_value + total_tax_value + total_expense_value) AS total_amount,
        created_by
    FROM erp_purchase_orders WHERE type = 'jo'
    UNION ALL
    SELECT 
        id AS document_id, 
        group_id, 
        company_id, 
        organization_id, 
        'ge' AS document_type, 
        book_id, 
        book_code COLLATE utf8mb4_general_ci as book_code, 
        document_number COLLATE utf8mb4_general_ci  AS document_number, 
        document_date, 
        revision_number, 
        revision_date, 
        vendor_id AS party_id, 
        vendor_code COLLATE utf8mb4_general_ci AS party_code, 
        'vendor' AS party_type, 
        currency_id, 
        currency_code, 
        document_status COLLATE utf8mb4_general_ci  AS document_status, 
        approval_level,
        total_amount,
        created_by
    FROM erp_gate_entry_headers
    UNION ALL
    SELECT 
        mrn.id AS document_id, 
        mrn.group_id, 
        mrn.company_id, 
        mrn.organization_id, 
        'mrn' AS document_type, 
        mrn.book_id, 
        mrn.book_code COLLATE utf8mb4_general_ci as book_code, 
        mrn.document_number COLLATE utf8mb4_general_ci  AS document_number, 
        mrn.document_date, 
        mrn.revision_number, 
        mrn.revision_date, 
        mrn.department_id AS party_id, 
        d.name COLLATE utf8_general_ci AS party_code, 
        'department' AS party_type, 
        mrn.currency_id, 
        mrn.currency_code, 
        mrn.document_status COLLATE utf8mb4_general_ci  AS document_status, 
        mrn.approval_level,
        mrn.total_amount,
        mrn.created_by
    FROM erp_mrn_headers AS mrn
    JOIN departments as d on d.id = mrn.department_id
    UNION ALL
    SELECT 
        id AS document_id, 
        group_id, 
        company_id, 
        organization_id, 
        'purchase-return' AS document_type, 
        book_id, 
        book_code COLLATE utf8mb4_general_ci as book_code, 
        document_number COLLATE utf8mb4_general_ci  AS document_number, 
        document_date, 
        revision_number, 
        revision_date, 
        vendor_id AS party_id, 
        vendor_code COLLATE utf8mb4_general_ci AS party_code, 
        'vendor' AS party_type, 
        currency_id, 
        currency_code, 
        document_status COLLATE utf8mb4_general_ci  AS document_status, 
        approval_level,
        total_amount,
        created_by
    FROM erp_purchase_return_headers
    UNION ALL
    SELECT 
        id AS document_id, 
        group_id, 
        company_id, 
        organization_id, 
        'pb' AS document_type, 
        book_id, 
        book_code COLLATE utf8mb4_general_ci as book_code, 
        document_number COLLATE utf8mb4_general_ci  AS document_number, 
        document_date, 
        revision_number, 
        revision_date, 
        vendor_id AS party_id, 
        vendor_code COLLATE utf8mb4_general_ci AS party_code, 
        'vendor' AS party_type,
        currency_id, 
        currency_code, 
        document_status COLLATE utf8mb4_general_ci  AS document_status, 
        approval_level,
        total_amount,
        created_by
    FROM erp_pb_headers
    UNION ALL
    SELECT 
        id AS document_id, 
        group_id, 
        company_id, 
        organization_id, 
        'expense-advice' AS document_type, 
        book_id, 
        book_code COLLATE utf8mb4_general_ci as book_code, 
        document_number COLLATE utf8mb4_general_ci  AS document_number, 
        document_date, 
        revision_number, 
        revision_date, 
        vendor_id AS party_id, 
        vendor_code COLLATE utf8mb4_general_ci AS party_code, 
        'vendor' AS party_type, 
        currency_id, 
        currency_code, 
        document_status COLLATE utf8mb4_general_ci  AS document_status, 
        approval_level,
        total_amount,
        created_by
    FROM erp_expense_headers
    UNION ALL
    SELECT 
        id AS document_id, 
        group_id, 
        company_id, 
        organization_id, 
        'mi' AS document_type, 
        book_id, 
        book_code COLLATE utf8mb4_general_ci as book_code, 
        document_number COLLATE utf8mb4_general_ci  AS document_number, 
        document_date, 
        revision_number, 
        revision_date, 
        CASE WHEN requester_type = 'user' THEN user_id ELSE department_id END AS party_id, 
        NULL AS party_code, 
        requester_type AS party_type, 
        currency_id, 
        currency_code, 
        document_status COLLATE utf8mb4_general_ci  AS document_status, 
        approval_level,
        (total_item_value - total_discount_value + total_tax_value + total_expense_value) AS total_amount,
        created_by
    FROM erp_material_issue_header
    UNION ALL
    SELECT 
        id AS document_id, 
        group_id, 
        company_id, 
        organization_id, 
        'mr' AS document_type, 
        book_id, 
        book_code COLLATE utf8mb4_general_ci as book_code, 
        document_number COLLATE utf8mb4_general_ci  AS document_number, 
        document_date, 
        revision_number, 
        revision_date, 
        CASE WHEN user_id IS NULL THEN department_id ELSE user_id END AS party_id, 
        CASE WHEN user_name IS NULL THEN department_code ELSE user_name END COLLATE utf8mb4_general_ci AS party_code, 
        CASE WHEN user_id IS NULL THEN "department" ELSE "user" END AS party_type, 
        currency_id, 
        currency_code, 
        document_status COLLATE utf8mb4_general_ci  AS document_status, 
        approval_level,
        total_amount,
        created_by
    FROM erp_material_return_header 

    UNION ALL
    SELECT 
        bom.id AS document_id, 
        bom.group_id, 
        bom.company_id, 
        bom.organization_id, 
        'bom' AS document_type, 
        bom.book_id, 
        bom.book_code COLLATE utf8mb4_general_ci as book_code, 
        bom.document_number COLLATE utf8mb4_general_ci  AS document_number, 
        bom.document_date, 
        bom.revision_number, 
        bom.revision_date, 
        bom.customer_id AS party_id, 
        c.customer_code COLLATE utf8mb4_general_ci AS party_code, 
        'customer' AS party_type, 
        NULL AS currency_id, 
        NULL AS currency_code, 
        bom.document_status COLLATE utf8mb4_general_ci  AS document_status, 
        bom.approval_level,
        (bom.total_item_value - ( bom.item_waste_amount + bom.header_waste_amount ) + ( bom.item_overhead_amount + bom.header_overhead_amount )) AS total_amount,
        bom.created_by
    FROM erp_boms as bom
    JOIN erp_customers as c on c.id = bom.customer_id
    WHERE type = 'bom'
    UNION ALL
    SELECT 
        bom.id AS document_id, 
        bom.group_id, 
        bom.company_id, 
        bom.organization_id, 
        'qbom' AS document_type, 
        bom.book_id, 
        bom.book_code COLLATE utf8mb4_general_ci as book_code, 
        bom.document_number COLLATE utf8mb4_general_ci  AS document_number, 
        bom.document_date, 
        bom.revision_number, 
        bom.revision_date, 
        bom.customer_id AS party_id, 
        c.customer_code COLLATE utf8mb4_general_ci AS party_code, 
        'customer' AS party_type, 
        NULL AS currency_id, 
        NULL AS currency_code, 
        bom.document_status COLLATE utf8mb4_general_ci  AS document_status, 
        bom.approval_level,
        (bom.total_item_value - ( bom.item_waste_amount + bom.header_waste_amount ) + ( bom.item_overhead_amount + bom.header_overhead_amount )) AS total_amount,
        bom.created_by
    FROM erp_boms as bom 
    JOIN erp_customers as c on c.id = bom.customer_id
    WHERE type = 'qbom'
    UNION ALL
    SELECT 
        pwo.id AS document_id, 
        pwo.group_id, 
        pwo.company_id, 
        pwo.organization_id, 
        'pwo' AS document_type, 
        pwo.book_id, 
        pwo.book_code COLLATE utf8mb4_general_ci as book_code, 
        pwo.document_number COLLATE utf8mb4_general_ci  AS document_number, 
        pwo.document_date, 
        pwo.revision_number, 
        pwo.revision_date, 
        so.customer_id AS party_id, 
        so.customer_code COLLATE utf8mb4_general_ci AS party_code, 
        'customer' AS party_type, 
        null AS currency_id, 
        null AS currency_code, 
        pwo.document_status COLLATE utf8mb4_general_ci  AS document_status, 
        pwo.approval_level,
        0 AS total_amount,
        pwo.created_by
    FROM erp_production_work_orders pwo
    JOIN erp_pwo_so_mapping mappings ON pwo.id = mappings.pwo_id
    JOIN erp_sale_orders so ON mappings.so_id = so.id
    UNION ALL
    SELECT 
        mfg.id AS document_id, 
        mfg.group_id, 
        mfg.company_id, 
        mfg.organization_id, 
        'mo' AS document_type, 
        mfg.book_id, 
        mfg.book_code COLLATE utf8mb4_general_ci as book_code, 
        mfg.document_number COLLATE utf8mb4_general_ci  AS document_number, 
        mfg.document_date, 
        mfg.revision_number, 
        mfg.revision_date, 
        c.id AS party_id, 
        c.customer_code COLLATE utf8mb4_general_ci AS party_code, 
        'customer' AS party_type, 
        mfg.currency_id, 
        mfg.currency_code, 
        mfg.document_status COLLATE utf8mb4_general_ci  AS document_status, 
        mfg.approval_level,
        0 AS total_amount,
        mfg.created_by
    FROM erp_mfg_orders mfg
    JOIN (
        SELECT *, ROW_NUMBER() OVER (PARTITION BY mo_id ORDER BY created_at ASC) AS row_num
        FROM erp_mo_products
    ) mo ON mfg.id = mo.mo_id AND mo.row_num = 1 
    JOIN erp_customers c ON mo.customer_id = c.id
    UNION ALL
    SELECT 
        pslip.id AS document_id, 
        pslip.group_id, 
        pslip.company_id, 
        pslip.organization_id, 
        'pslip' AS document_type, 
        pslip.book_id, 
        pslip.book_code COLLATE utf8mb4_general_ci as book_code, 
        pslip.document_number COLLATE utf8mb4_general_ci  AS document_number, 
        pslip.document_date, 
        pslip.revision_number, 
        pslip.revision_date, 
        c.id AS party_id, 
        c.customer_code COLLATE utf8mb4_general_ci AS party_code, 
        'customer' AS party_type, 
        null AS currency_id, 
        null AS currency_code, 
        pslip.document_status COLLATE utf8mb4_general_ci  AS document_status, 
        pslip.approval_level,
        (pslip.total_item_value - total_discount_value + total_tax_value + total_expense_value) AS total_amount,
        pslip.created_by
    FROM erp_production_slips pslip
    JOIN (
        SELECT *, ROW_NUMBER() OVER (PARTITION BY pslip_id ORDER BY created_at ASC) AS row_num
        FROM erp_pslip_items
    ) pslipI ON pslip.id = pslipI.pslip_id AND pslipI.row_num = 1 
    JOIN erp_customers c ON pslipI.customer_id = c.id
    UNION ALL
    SELECT 
        id AS document_id, 
        group_id, 
        company_id, 
        organization_id, 
        'sq' AS document_type, 
        book_id, 
        book_code COLLATE utf8mb4_general_ci as book_code, 
        document_number COLLATE utf8mb4_general_ci  AS document_number, 
        document_date, 
        revision_number, 
        revision_date, 
        customer_id AS party_id, 
        customer_code COLLATE utf8mb4_general_ci AS party_code, 
        'customer' AS party_type, 
        currency_id, 
        currency_code, 
        document_status COLLATE utf8mb4_general_ci  AS document_status, 
        approval_level,
        (total_item_value - total_discount_value + total_tax_value + total_expense_value) AS total_amount,
        created_by
    FROM erp_sale_orders WHERE document_type ='sq'
    UNION ALL
    SELECT 
        id AS document_id, 
        group_id, 
        company_id, 
        organization_id, 
        'so' AS document_type, 
        book_id, 
        book_code COLLATE utf8mb4_general_ci as book_code, 
        document_number COLLATE utf8mb4_general_ci  AS document_number, 
        document_date, 
        revision_number, 
        revision_date, 
        customer_id AS party_id, 
        customer_code COLLATE utf8mb4_general_ci AS party_code, 
        'customer' AS party_type, 
        currency_id, 
        currency_code, 
        document_status COLLATE utf8mb4_general_ci  AS document_status, 
        approval_level,
        (total_item_value - total_discount_value + total_tax_value + total_expense_value) AS total_amount,
        created_by
    FROM erp_sale_orders WHERE document_type ='so'
    UNION ALL
    SELECT 
        id AS document_id, 
        group_id, 
        company_id, 
        organization_id, 
        'dnote' AS document_type, 
        book_id, 
        book_code COLLATE utf8mb4_general_ci as book_code, 
        document_number COLLATE utf8mb4_general_ci  AS document_number, 
        document_date, 
        revision_number, 
        revision_date, 
        customer_id AS party_id, 
        customer_code COLLATE utf8mb4_general_ci AS party_code, 
        'customer' AS party_type, 
        currency_id, 
        currency_code, 
        document_status COLLATE utf8mb4_general_ci  AS document_status, 
        approval_level,
        (total_item_value - total_discount_value + total_tax_value + total_expense_value) AS total_amount,
        created_by
    FROM erp_sale_invoices WHERE document_type ='dnote' AND invoice_required = 1
    UNION ALL
    SELECT 
        id AS document_id, 
        group_id, 
        company_id, 
        organization_id, 
        'sinvdnote' AS document_type, 
        book_id, 
        book_code COLLATE utf8mb4_general_ci as book_code, 
        document_number COLLATE utf8mb4_general_ci  AS document_number, 
        document_date, 
        revision_number, 
        revision_date, 
        customer_id AS party_id, 
        customer_code COLLATE utf8mb4_general_ci AS party_code, 
        'customer' AS party_type, 
        currency_id, 
        currency_code, 
        document_status COLLATE utf8mb4_general_ci  AS document_status, 
        approval_level,
        (total_item_value - total_discount_value + total_tax_value + total_expense_value) AS total_amount,
        created_by
    FROM erp_sale_invoices WHERE document_type ='dnote' AND invoice_required = 0
    UNION ALL
    SELECT 
        id AS document_id, 
        group_id, 
        company_id, 
        organization_id, 
        'sinvdnote' AS document_type, 
        book_id, 
        book_code COLLATE utf8mb4_general_ci as book_code, 
        document_number COLLATE utf8mb4_general_ci  AS document_number, 
        document_date, 
        revision_number, 
        revision_date, 
        customer_id AS party_id, 
        customer_code COLLATE utf8mb4_general_ci AS party_code, 
        'customer' AS party_type, 
        currency_id, 
        currency_code, 
        document_status COLLATE utf8mb4_general_ci  AS document_status, 
        approval_level,
        (total_item_value - total_discount_value + total_tax_value + total_expense_value) AS total_amount,
        created_by
    FROM erp_sale_invoices WHERE document_type ='dnote' AND invoice_required = 1
    UNION ALL
    SELECT 
        id AS document_id, 
        group_id, 
        company_id, 
        organization_id, 
        'si' AS document_type, 
        book_id, 
        book_code COLLATE utf8mb4_general_ci as book_code, 
        document_number COLLATE utf8mb4_general_ci  AS document_number, 
        document_date, 
        revision_number, 
        revision_date, 
        customer_id AS party_id, 
        customer_code COLLATE utf8mb4_general_ci AS party_code, 
        'customer' AS party_type, 
        currency_id, 
        currency_code, 
        document_status COLLATE utf8mb4_general_ci  AS document_status, 
        approval_level,
        (total_item_value - total_discount_value + total_tax_value + total_expense_value) AS total_amount,
        created_by
    FROM erp_sale_invoices WHERE document_type ='si'
    UNION ALL
    SELECT 
        id AS document_id, 
        group_id, 
        company_id, 
        organization_id, 
        'sr' AS document_type, 
        book_id, 
        book_code COLLATE utf8mb4_general_ci as book_code, 
        document_number COLLATE utf8mb4_general_ci  AS document_number, 
        document_date, 
        revision_number, 
        revision_date, 
        customer_id AS party_id, 
        customer_code COLLATE utf8mb4_general_ci AS party_code, 
        'customer' AS party_type, 
        currency_id, 
        currency_code, 
        document_status COLLATE utf8mb4_general_ci  AS document_status, 
        approval_level,
        (total_return_value - total_discount_value + total_tax_value + total_expense_value) AS total_amount,
        created_by
    FROM erp_sale_returns
) AS transactions
