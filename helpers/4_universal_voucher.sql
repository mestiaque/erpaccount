-- ==========================================
-- Demo Data: Universal Voucher
-- Tables: acc_journal_masters, acc_journal_details
-- Prerequisite: 1_chart_of_accounts.sql
-- Optional: 6_cost_centers.sql (if cost_center linking needed)
-- ==========================================

START TRANSACTION;

DELETE d
FROM acc_journal_details d
JOIN acc_journal_masters m ON m.journal_id = d.journal_id
WHERE m.voucher_no IN ('JV-2026-0001', 'JV-2026-0002', 'JV-2026-0003', 'JV-2026-0004', 'JV-2026-0005', 'JV-2026-0006', 'JV-2026-0007');

DELETE FROM acc_journal_masters
WHERE voucher_no IN ('JV-2026-0001', 'JV-2026-0002', 'JV-2026-0003', 'JV-2026-0004', 'JV-2026-0005', 'JV-2026-0006', 'JV-2026-0007');

-- 1) Credit Sales Journal
INSERT INTO acc_journal_masters
(voucher_no, journal_date, source_module, source_reference_id, narration, created_by, is_voided, voided_at, voided_by, void_reason, created_at, updated_at)
VALUES
('JV-2026-0001', '2026-05-10', 'Manual', 50001, 'Credit sales posted for export buyer invoice INV-EXP-001', 1, 0, NULL, NULL, NULL, NOW(), NOW());
SET @jv1 := LAST_INSERT_ID();

INSERT INTO acc_journal_details
(journal_id, account_id, cost_center_id, party_type, party_id, debit_amount, credit_amount, is_reconciled, reconciled_at, matched_statement_id, reconciliation_note, created_at, updated_at)
SELECT @jv1, coa.account_id, cc.cost_center_id, 'Buyer', 9001, 350000.00, 0.00, 0, NULL, NULL, NULL, NOW(), NOW()
FROM acc_chart_of_accounts coa
LEFT JOIN acc_cost_centers cc ON cc.cost_center_name = 'Order #ORD-2026-001'
WHERE coa.account_code = '1200-01';

INSERT INTO acc_journal_details
(journal_id, account_id, cost_center_id, party_type, party_id, debit_amount, credit_amount, is_reconciled, reconciled_at, matched_statement_id, reconciliation_note, created_at, updated_at)
SELECT @jv1, coa.account_id, cc.cost_center_id, 'None', NULL, 0.00, 350000.00, 0, NULL, NULL, NULL, NOW(), NOW()
FROM acc_chart_of_accounts coa
LEFT JOIN acc_cost_centers cc ON cc.cost_center_name = 'Order #ORD-2026-001'
WHERE coa.account_code = '4100-01';

-- 2) Supplier Bill Accrual
INSERT INTO acc_journal_masters
(voucher_no, journal_date, source_module, source_reference_id, narration, created_by, is_voided, voided_at, voided_by, void_reason, created_at, updated_at)
VALUES
('JV-2026-0002', '2026-05-12', 'Manual', 50002, 'Fabric purchase booked on supplier credit bill BILL-FAB-077', 1, 0, NULL, NULL, NULL, NOW(), NOW());
SET @jv2 := LAST_INSERT_ID();

INSERT INTO acc_journal_details
(journal_id, account_id, cost_center_id, party_type, party_id, debit_amount, credit_amount, is_reconciled, reconciled_at, matched_statement_id, reconciliation_note, created_at, updated_at)
SELECT @jv2, coa.account_id, cc.cost_center_id, 'None', NULL, 180000.00, 0.00, 0, NULL, NULL, NULL, NOW(), NOW()
FROM acc_chart_of_accounts coa
LEFT JOIN acc_cost_centers cc ON cc.cost_center_name = 'Production Department'
WHERE coa.account_code = '5100-01';

INSERT INTO acc_journal_details
(journal_id, account_id, cost_center_id, party_type, party_id, debit_amount, credit_amount, is_reconciled, reconciled_at, matched_statement_id, reconciliation_note, created_at, updated_at)
SELECT @jv2, coa.account_id, cc.cost_center_id, 'Supplier', 7001, 0.00, 180000.00, 0, NULL, NULL, NULL, NOW(), NOW()
FROM acc_chart_of_accounts coa
LEFT JOIN acc_cost_centers cc ON cc.cost_center_name = 'Production Department'
WHERE coa.account_code = '2100-01';

-- 3) Salary Accrual Journal
INSERT INTO acc_journal_masters
(voucher_no, journal_date, source_module, source_reference_id, narration, created_by, is_voided, voided_at, voided_by, void_reason, created_at, updated_at)
VALUES
('JV-2026-0003', '2026-05-31', 'Payroll', 50003, 'Monthly salary accrual for factory and staff', 1, 0, NULL, NULL, NULL, NOW(), NOW());
SET @jv3 := LAST_INSERT_ID();

INSERT INTO acc_journal_details
(journal_id, account_id, cost_center_id, party_type, party_id, debit_amount, credit_amount, is_reconciled, reconciled_at, matched_statement_id, reconciliation_note, created_at, updated_at)
SELECT @jv3, coa.account_id, cc.cost_center_id, 'None', NULL, 140000.00, 0.00, 0, NULL, NULL, NULL, NOW(), NOW()
FROM acc_chart_of_accounts coa
LEFT JOIN acc_cost_centers cc ON cc.cost_center_name = 'Production Department'
WHERE coa.account_code = '5200';

INSERT INTO acc_journal_details
(journal_id, account_id, cost_center_id, party_type, party_id, debit_amount, credit_amount, is_reconciled, reconciled_at, matched_statement_id, reconciliation_note, created_at, updated_at)
SELECT @jv3, coa.account_id, cc.cost_center_id, 'Employee', 8001, 0.00, 140000.00, 0, NULL, NULL, NULL, NOW(), NOW()
FROM acc_chart_of_accounts coa
LEFT JOIN acc_cost_centers cc ON cc.cost_center_name = 'Accounts & Finance'
WHERE coa.account_code = '2300-02';

-- 4) Utilities Accrual Journal
INSERT INTO acc_journal_masters
(voucher_no, journal_date, source_module, source_reference_id, narration, created_by, is_voided, voided_at, voided_by, void_reason, created_at, updated_at)
VALUES
('JV-2026-0004', '2026-06-02', 'Manual', 50004, 'Monthly utility accrual for power and gas', 1, 0, NULL, NULL, NULL, NOW(), NOW());
SET @jv4 := LAST_INSERT_ID();

INSERT INTO acc_journal_details
(journal_id, account_id, cost_center_id, party_type, party_id, debit_amount, credit_amount, is_reconciled, reconciled_at, matched_statement_id, reconciliation_note, created_at, updated_at)
SELECT @jv4, coa.account_id, cc.cost_center_id, 'None', NULL, 62000.00, 0.00, 0, NULL, NULL, NULL, NOW(), NOW()
FROM acc_chart_of_accounts coa
LEFT JOIN acc_cost_centers cc ON cc.cost_center_name = 'Production Department'
WHERE coa.account_code = '6100';

INSERT INTO acc_journal_details
(journal_id, account_id, cost_center_id, party_type, party_id, debit_amount, credit_amount, is_reconciled, reconciled_at, matched_statement_id, reconciliation_note, created_at, updated_at)
SELECT @jv4, coa.account_id, cc.cost_center_id, 'None', NULL, 0.00, 62000.00, 0, NULL, NULL, NULL, NOW(), NOW()
FROM acc_chart_of_accounts coa
LEFT JOIN acc_cost_centers cc ON cc.cost_center_name = 'Accounts & Finance'
WHERE coa.account_code = '2300-03';

-- 5) Inventory Adjustment Journal
INSERT INTO acc_journal_masters
(voucher_no, journal_date, source_module, source_reference_id, narration, created_by, is_voided, voided_at, voided_by, void_reason, created_at, updated_at)
VALUES
('JV-2026-0005', '2026-06-04', 'Inventory', 50005, 'Inventory write-off and adjustment entry', 1, 0, NULL, NULL, NULL, NOW(), NOW());
SET @jv5 := LAST_INSERT_ID();

INSERT INTO acc_journal_details
(journal_id, account_id, cost_center_id, party_type, party_id, debit_amount, credit_amount, is_reconciled, reconciled_at, matched_statement_id, reconciliation_note, created_at, updated_at)
SELECT @jv5, coa.account_id, cc.cost_center_id, 'None', NULL, 28500.00, 0.00, 0, NULL, NULL, NULL, NOW(), NOW()
FROM acc_chart_of_accounts coa
LEFT JOIN acc_cost_centers cc ON cc.cost_center_name = 'Line C - Finishing'
WHERE coa.account_code = '5100-03';

INSERT INTO acc_journal_details
(journal_id, account_id, cost_center_id, party_type, party_id, debit_amount, credit_amount, is_reconciled, reconciled_at, matched_statement_id, reconciliation_note, created_at, updated_at)
SELECT @jv5, coa.account_id, cc.cost_center_id, 'None', NULL, 0.00, 28500.00, 0, NULL, NULL, NULL, NOW(), NOW()
FROM acc_chart_of_accounts coa
LEFT JOIN acc_cost_centers cc ON cc.cost_center_name = 'Line C - Finishing'
WHERE coa.account_code = '1300-03';

-- 6) Owner Capital Injection (Equity impact)
INSERT INTO acc_journal_masters
(voucher_no, journal_date, source_module, source_reference_id, narration, created_by, is_voided, voided_at, voided_by, void_reason, created_at, updated_at)
VALUES
('JV-2026-0006', '2026-06-10', 'Manual', 50006, 'Additional capital introduced by managing director', 1, 0, NULL, NULL, NULL, NOW(), NOW());
SET @jv6 := LAST_INSERT_ID();

INSERT INTO acc_journal_details
(journal_id, account_id, cost_center_id, party_type, party_id, debit_amount, credit_amount, is_reconciled, reconciled_at, matched_statement_id, reconciliation_note, created_at, updated_at)
SELECT @jv6, coa.account_id, NULL, 'None', NULL, 450000.00, 0.00, 0, NULL, NULL, NULL, NOW(), NOW()
FROM acc_chart_of_accounts coa
WHERE coa.account_code = '1100-03';

INSERT INTO acc_journal_details
(journal_id, account_id, cost_center_id, party_type, party_id, debit_amount, credit_amount, is_reconciled, reconciled_at, matched_statement_id, reconciliation_note, created_at, updated_at)
SELECT @jv6, coa.account_id, NULL, 'None', NULL, 0.00, 450000.00, 0, NULL, NULL, NULL, NOW(), NOW()
FROM acc_chart_of_accounts coa
WHERE coa.account_code = '3100-01';

-- 7) Profit Transfer to Retained Earnings (Equity movement)
INSERT INTO acc_journal_masters
(voucher_no, journal_date, source_module, source_reference_id, narration, created_by, is_voided, voided_at, voided_by, void_reason, created_at, updated_at)
VALUES
('JV-2026-0007', '2026-06-30', 'Manual', 50007, 'Transfer period profit to retained earnings for presentation', 1, 0, NULL, NULL, NULL, NOW(), NOW());
SET @jv7 := LAST_INSERT_ID();

INSERT INTO acc_journal_details
(journal_id, account_id, cost_center_id, party_type, party_id, debit_amount, credit_amount, is_reconciled, reconciled_at, matched_statement_id, reconciliation_note, created_at, updated_at)
SELECT @jv7, coa.account_id, NULL, 'None', NULL, 125000.00, 0.00, 0, NULL, NULL, NULL, NOW(), NOW()
FROM acc_chart_of_accounts coa
WHERE coa.account_code = '4100-01';

INSERT INTO acc_journal_details
(journal_id, account_id, cost_center_id, party_type, party_id, debit_amount, credit_amount, is_reconciled, reconciled_at, matched_statement_id, reconciliation_note, created_at, updated_at)
SELECT @jv7, coa.account_id, NULL, 'None', NULL, 0.00, 125000.00, 0, NULL, NULL, NULL, NOW(), NOW()
FROM acc_chart_of_accounts coa
WHERE coa.account_code = '3200-01';

COMMIT;
