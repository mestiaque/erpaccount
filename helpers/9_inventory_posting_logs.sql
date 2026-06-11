-- ==========================================
-- Demo Data: Inventory Posting Logs
-- Table: acc_inventory_posting_logs
-- ==========================================

START TRANSACTION;

DELETE FROM acc_inventory_posting_logs
WHERE reference_no IN ('GRN-YARN-2026-0007', 'ISS-FAB-2026-0019', 'GRN-TRIMS-2026-0012', 'ISS-YARN-2026-0022', 'GRN-FAB-2026-0015', 'ISS-TRIMS-2026-0011');

INSERT INTO acc_inventory_posting_logs
(source_module, transaction_type, reference_no, description, system_valuation, override_valuation, posting_status, journal_id, payload, reviewed_by, reviewed_at, created_at, updated_at)
VALUES
(
    'Inventory',
    'Yarn_Purchase_GRN',
    'GRN-YARN-2026-0007',
    'Yarn purchase GRN posted from inventory module',
    178500.00,
    NULL,
    'Posted',
    (SELECT journal_id FROM acc_journal_masters WHERE voucher_no = 'JV-2026-0002' LIMIT 1),
    JSON_OBJECT('supplier_id', 7001, 'warehouse_id', 5, 'uom', 'KG', 'qty', 3200),
    3,
    NOW(),
    NOW(),
    NOW()
),
(
    'Production',
    'Fabric_Issue',
    'ISS-FAB-2026-0019',
    'Fabric issued to line A for production lot 19',
    92000.00,
    94000.00,
    'Overridden',
    NULL,
    JSON_OBJECT('line', 'A', 'lot_no', 'LOT-19', 'qty_mtr', 5600),
    4,
    NOW(),
    NOW(),
    NOW()
),
(
    'Commercial',
    'Goods_Receipt',
    'GRN-TRIMS-2026-0012',
    'Imported trims received under BTB LC',
    64000.00,
    NULL,
    'Pending Review',
    NULL,
    JSON_OBJECT('lc_ref', 30002, 'item_group', 'Trims', 'cartons', 48),
    NULL,
    NULL,
    NOW(),
    NOW()
),
(
    'Production',
    'Material_Issue',
    'ISS-YARN-2026-0022',
    'Yarn issue to knitting section for order ORD-2026-003',
    81000.00,
    NULL,
    'Posted',
    (SELECT journal_id FROM acc_journal_masters WHERE voucher_no = 'JV-2026-0005' LIMIT 1),
    JSON_OBJECT('line', 'C', 'lot_no', 'LOT-22', 'qty_kg', 1450),
    5,
    NOW(),
    NOW(),
    NOW()
),
(
    'Inventory',
    'Goods_Receipt',
    'GRN-FAB-2026-0015',
    'Finished fabric received from dyeing',
    116000.00,
    118000.00,
    'Overridden',
    NULL,
    JSON_OBJECT('warehouse_id', 8, 'rolls', 92, 'shade', 'Navy'),
    6,
    NOW(),
    NOW(),
    NOW()
),
(
    'Production',
    'Trim_Purchase',
    'ISS-TRIMS-2026-0011',
    'Trims issued to finishing section',
    27500.00,
    NULL,
    'Pending Review',
    NULL,
    JSON_OBJECT('line', 'D', 'cartons', 12, 'item_group', 'Buttons'),
    NULL,
    NULL,
    NOW(),
    NOW()
);

COMMIT;
