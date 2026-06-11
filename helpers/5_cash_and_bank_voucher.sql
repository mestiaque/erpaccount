-- ==========================================
-- Demo Data: Cash & Bank Voucher
-- Tables: acc_journal_masters, acc_journal_details
-- Prerequisite: 1_chart_of_accounts.sql
-- Optional: 6_cost_centers.sql (if cost_center linking needed)
-- ==========================================

START TRANSACTION;

DELETE d
FROM acc_journal_details d
JOIN acc_journal_masters m ON m.journal_id = d.journal_id
WHERE m.voucher_no IN ('BPV-2026-0001', 'CRV-2026-0001', 'BRV-2026-0001', 'CPV-2026-VOID-01', 'BPV-2026-0002', 'CRV-2026-0002', 'BRV-2026-0002');

DELETE FROM acc_journal_masters
WHERE voucher_no IN ('BPV-2026-0001', 'CRV-2026-0001', 'BRV-2026-0001', 'CPV-2026-VOID-01', 'BPV-2026-0002', 'CRV-2026-0002', 'BRV-2026-0002');

-- 1) Bank Payment Voucher (Supplier Payment)
INSERT INTO acc_journal_masters
(voucher_no, journal_date, source_module, source_reference_id, narration, created_by, is_voided, voided_at, voided_by, void_reason, created_at, updated_at)
VALUES
('BPV-2026-0001', '2026-05-14', 'Manual', 61001, 'Paid supplier bill via BRAC bank transfer', 1, 0, NULL, NULL, NULL, NOW(), NOW());
SET @bpv1 := LAST_INSERT_ID();

INSERT INTO acc_journal_details
(journal_id, account_id, cost_center_id, party_type, party_id, debit_amount, credit_amount, is_reconciled, reconciled_at, matched_statement_id, reconciliation_note, created_at, updated_at)
SELECT @bpv1, coa.account_id, cc.cost_center_id, 'Supplier', 7001, 120000.00, 0.00, 0, NULL, NULL, NULL, NOW(), NOW()
FROM acc_chart_of_accounts coa
LEFT JOIN acc_cost_centers cc ON cc.cost_center_name = 'Production Department'
WHERE coa.account_code = '2100-01';

INSERT INTO acc_journal_details
(journal_id, account_id, cost_center_id, party_type, party_id, debit_amount, credit_amount, is_reconciled, reconciled_at, matched_statement_id, reconciliation_note, created_at, updated_at)
SELECT @bpv1, coa.account_id, NULL, 'None', NULL, 0.00, 120000.00, 0, NULL, NULL, NULL, NOW(), NOW()
FROM acc_chart_of_accounts coa
WHERE coa.account_code = '1100-03';

-- 2) Cash Receipt Voucher (Local Sales)
INSERT INTO acc_journal_masters
(voucher_no, journal_date, source_module, source_reference_id, narration, created_by, is_voided, voided_at, voided_by, void_reason, created_at, updated_at)
VALUES
('CRV-2026-0001', '2026-05-16', 'Manual', 61002, 'Cash received against local sales invoice INV-LOC-021', 1, 0, NULL, NULL, NULL, NOW(), NOW());
SET @crv1 := LAST_INSERT_ID();

INSERT INTO acc_journal_details
(journal_id, account_id, cost_center_id, party_type, party_id, debit_amount, credit_amount, is_reconciled, reconciled_at, matched_statement_id, reconciliation_note, created_at, updated_at)
SELECT @crv1, coa.account_id, NULL, 'None', NULL, 85000.00, 0.00, 0, NULL, NULL, NULL, NOW(), NOW()
FROM acc_chart_of_accounts coa
WHERE coa.account_code = '1100-01';

INSERT INTO acc_journal_details
(journal_id, account_id, cost_center_id, party_type, party_id, debit_amount, credit_amount, is_reconciled, reconciled_at, matched_statement_id, reconciliation_note, created_at, updated_at)
SELECT @crv1, coa.account_id, cc.cost_center_id, 'Buyer', 9102, 0.00, 85000.00, 0, NULL, NULL, NULL, NOW(), NOW()
FROM acc_chart_of_accounts coa
LEFT JOIN acc_cost_centers cc ON cc.cost_center_name = 'Order #ORD-2026-002'
WHERE coa.account_code = '4100-02';

-- 3) Bank Receipt Voucher (Buyer Collection)
INSERT INTO acc_journal_masters
(voucher_no, journal_date, source_module, source_reference_id, narration, created_by, is_voided, voided_at, voided_by, void_reason, created_at, updated_at)
VALUES
('BRV-2026-0001', '2026-05-18', 'Manual', 61003, 'Collection received from export buyer via bank', 1, 0, NULL, NULL, NULL, NOW(), NOW());
SET @brv1 := LAST_INSERT_ID();

INSERT INTO acc_journal_details
(journal_id, account_id, cost_center_id, party_type, party_id, debit_amount, credit_amount, is_reconciled, reconciled_at, matched_statement_id, reconciliation_note, created_at, updated_at)
SELECT @brv1, coa.account_id, NULL, 'None', NULL, 275000.00, 0.00, 0, NULL, NULL, NULL, NOW(), NOW()
FROM acc_chart_of_accounts coa
WHERE coa.account_code = '1100-03';

INSERT INTO acc_journal_details
(journal_id, account_id, cost_center_id, party_type, party_id, debit_amount, credit_amount, is_reconciled, reconciled_at, matched_statement_id, reconciliation_note, created_at, updated_at)
SELECT @brv1, coa.account_id, cc.cost_center_id, 'Buyer', 9001, 0.00, 275000.00, 0, NULL, NULL, NULL, NOW(), NOW()
FROM acc_chart_of_accounts coa
LEFT JOIN acc_cost_centers cc ON cc.cost_center_name = 'Order #ORD-2026-001'
WHERE coa.account_code = '1200-01';

-- 4) Voided Voucher Example (for presentation)
INSERT INTO acc_journal_masters
(voucher_no, journal_date, source_module, source_reference_id, narration, created_by, is_voided, voided_at, voided_by, void_reason, created_at, updated_at)
VALUES
('CPV-2026-VOID-01', '2026-05-19', 'Manual', 61004, 'Erroneous petty cash payment entry', 1, 1, NOW(), 2, 'Duplicate entry detected during review', NOW(), NOW());
SET @void1 := LAST_INSERT_ID();

INSERT INTO acc_journal_details
(journal_id, account_id, cost_center_id, party_type, party_id, debit_amount, credit_amount, is_reconciled, reconciled_at, matched_statement_id, reconciliation_note, created_at, updated_at)
SELECT @void1, coa.account_id, NULL, 'None', NULL, 15000.00, 0.00, 0, NULL, NULL, NULL, NOW(), NOW()
FROM acc_chart_of_accounts coa
WHERE coa.account_code = '6200';

INSERT INTO acc_journal_details
(journal_id, account_id, cost_center_id, party_type, party_id, debit_amount, credit_amount, is_reconciled, reconciled_at, matched_statement_id, reconciliation_note, created_at, updated_at)
SELECT @void1, coa.account_id, NULL, 'None', NULL, 0.00, 15000.00, 0, NULL, NULL, NULL, NOW(), NOW()
FROM acc_chart_of_accounts coa
WHERE coa.account_code = '1100-02';

-- 5) Bank Payment Voucher (Utility Bill)
INSERT INTO acc_journal_masters
(voucher_no, journal_date, source_module, source_reference_id, narration, created_by, is_voided, voided_at, voided_by, void_reason, created_at, updated_at)
VALUES
('BPV-2026-0002', '2026-06-06', 'Manual', 61005, 'Utility bill paid through EBL account', 1, 0, NULL, NULL, NULL, NOW(), NOW());
SET @bpv2 := LAST_INSERT_ID();

INSERT INTO acc_journal_details
(journal_id, account_id, cost_center_id, party_type, party_id, debit_amount, credit_amount, is_reconciled, reconciled_at, matched_statement_id, reconciliation_note, created_at, updated_at)
SELECT @bpv2, coa.account_id, cc.cost_center_id, 'None', NULL, 42000.00, 0.00, 0, NULL, NULL, NULL, NOW(), NOW()
FROM acc_chart_of_accounts coa
LEFT JOIN acc_cost_centers cc ON cc.cost_center_name = 'Accounts & Finance'
WHERE coa.account_code = '6100';

INSERT INTO acc_journal_details
(journal_id, account_id, cost_center_id, party_type, party_id, debit_amount, credit_amount, is_reconciled, reconciled_at, matched_statement_id, reconciliation_note, created_at, updated_at)
SELECT @bpv2, coa.account_id, NULL, 'None', NULL, 0.00, 42000.00, 0, NULL, NULL, NULL, NOW(), NOW()
FROM acc_chart_of_accounts coa
WHERE coa.account_code = '1100-04';

-- 6) Cash Receipt Voucher (Scrap Sale)
INSERT INTO acc_journal_masters
(voucher_no, journal_date, source_module, source_reference_id, narration, created_by, is_voided, voided_at, voided_by, void_reason, created_at, updated_at)
VALUES
('CRV-2026-0002', '2026-06-07', 'Manual', 61006, 'Cash received from scrap and waste sales', 1, 0, NULL, NULL, NULL, NOW(), NOW());
SET @crv2 := LAST_INSERT_ID();

INSERT INTO acc_journal_details
(journal_id, account_id, cost_center_id, party_type, party_id, debit_amount, credit_amount, is_reconciled, reconciled_at, matched_statement_id, reconciliation_note, created_at, updated_at)
SELECT @crv2, coa.account_id, NULL, 'None', NULL, 23500.00, 0.00, 0, NULL, NULL, NULL, NOW(), NOW()
FROM acc_chart_of_accounts coa
WHERE coa.account_code = '1100-02';

INSERT INTO acc_journal_details
(journal_id, account_id, cost_center_id, party_type, party_id, debit_amount, credit_amount, is_reconciled, reconciled_at, matched_statement_id, reconciliation_note, created_at, updated_at)
SELECT @crv2, coa.account_id, cc.cost_center_id, 'None', NULL, 0.00, 23500.00, 0, NULL, NULL, NULL, NOW(), NOW()
FROM acc_chart_of_accounts coa
LEFT JOIN acc_cost_centers cc ON cc.cost_center_name = 'Line D - Packing'
WHERE coa.account_code = '4200-01';

-- 7) Bank Receipt Voucher (Second Buyer Collection)
INSERT INTO acc_journal_masters
(voucher_no, journal_date, source_module, source_reference_id, narration, created_by, is_voided, voided_at, voided_by, void_reason, created_at, updated_at)
VALUES
('BRV-2026-0002', '2026-06-08', 'Manual', 61007, 'Second export proceeds received via bank', 1, 0, NULL, NULL, NULL, NOW(), NOW());
SET @brv2 := LAST_INSERT_ID();

INSERT INTO acc_journal_details
(journal_id, account_id, cost_center_id, party_type, party_id, debit_amount, credit_amount, is_reconciled, reconciled_at, matched_statement_id, reconciliation_note, created_at, updated_at)
SELECT @brv2, coa.account_id, NULL, 'None', NULL, 190000.00, 0.00, 0, NULL, NULL, NULL, NOW(), NOW()
FROM acc_chart_of_accounts coa
WHERE coa.account_code = '1100-04';

INSERT INTO acc_journal_details
(journal_id, account_id, cost_center_id, party_type, party_id, debit_amount, credit_amount, is_reconciled, reconciled_at, matched_statement_id, reconciliation_note, created_at, updated_at)
SELECT @brv2, coa.account_id, cc.cost_center_id, 'Buyer', 9002, 0.00, 190000.00, 0, NULL, NULL, NULL, NOW(), NOW()
FROM acc_chart_of_accounts coa
LEFT JOIN acc_cost_centers cc ON cc.cost_center_name = 'Order #ORD-2026-003'
WHERE coa.account_code = '1200-01';

COMMIT;
