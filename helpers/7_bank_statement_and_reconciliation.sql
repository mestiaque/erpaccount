-- ==========================================
-- Demo Data: Bank Statement & Reconciliation
-- Tables: acc_bank_statement_entries, acc_journal_details
-- Prerequisite: 2_bank_and_cash_accounts.sql, 5_cash_and_bank_voucher.sql
-- ==========================================

START TRANSACTION;

DELETE FROM acc_bank_statement_entries
WHERE reference_no IN ('TXN-BPV-001', 'TXN-BRV-001', 'TXN-CHG-001', 'TXN-BPV-002', 'TXN-BRV-002', 'TXN-DEP-001');

-- Statement line 1: supplier payment (matches BPV-2026-0001 bank credit line)
INSERT INTO acc_bank_statement_entries
(bank_account_id, statement_date, reference_no, description, debit_amount, credit_amount, closing_balance, is_reconciled, reconciled_at, matched_detail_id, created_at, updated_at)
SELECT ba.bank_account_id, '2026-05-14', 'TXN-BPV-001', 'Online transfer to supplier - BILL-FAB-077', 0.00, 120000.00, 1880000.00, 0, NULL, NULL, NOW(), NOW()
FROM acc_bank_accounts ba
WHERE ba.account_number = '0109012345678';

SET @stmt1 := LAST_INSERT_ID();

UPDATE acc_bank_statement_entries s
JOIN acc_journal_details jd ON jd.detail_id = (
    SELECT d.detail_id
    FROM acc_journal_details d
    JOIN acc_journal_masters jm ON jm.journal_id = d.journal_id
    JOIN acc_chart_of_accounts coa ON coa.account_id = d.account_id
    WHERE jm.voucher_no = 'BPV-2026-0001'
      AND coa.account_code = '1100-03'
      AND d.credit_amount = 120000.00
    LIMIT 1
)
SET s.is_reconciled = 1,
    s.reconciled_at = NOW(),
    s.matched_detail_id = jd.detail_id
WHERE s.statement_id = @stmt1;

UPDATE acc_journal_details jd
SET jd.is_reconciled = 1,
    jd.reconciled_at = NOW(),
    jd.matched_statement_id = @stmt1,
    jd.reconciliation_note = 'Auto matched with bank statement TXN-BPV-001'
WHERE jd.detail_id = (
    SELECT t.detail_id FROM (
        SELECT d.detail_id
        FROM acc_journal_details d
        JOIN acc_journal_masters jm ON jm.journal_id = d.journal_id
        JOIN acc_chart_of_accounts coa ON coa.account_id = d.account_id
        WHERE jm.voucher_no = 'BPV-2026-0001'
          AND coa.account_code = '1100-03'
          AND d.credit_amount = 120000.00
        LIMIT 1
    ) AS t
);

-- Statement line 2: buyer collection (matches BRV-2026-0001 bank debit line)
INSERT INTO acc_bank_statement_entries
(bank_account_id, statement_date, reference_no, description, debit_amount, credit_amount, closing_balance, is_reconciled, reconciled_at, matched_detail_id, created_at, updated_at)
SELECT ba.bank_account_id, '2026-05-18', 'TXN-BRV-001', 'Export proceeds received - buyer remittance', 275000.00, 0.00, 2155000.00, 0, NULL, NULL, NOW(), NOW()
FROM acc_bank_accounts ba
WHERE ba.account_number = '0109012345678';

SET @stmt2 := LAST_INSERT_ID();

UPDATE acc_bank_statement_entries s
JOIN acc_journal_details jd ON jd.detail_id = (
    SELECT d.detail_id
    FROM acc_journal_details d
    JOIN acc_journal_masters jm ON jm.journal_id = d.journal_id
    JOIN acc_chart_of_accounts coa ON coa.account_id = d.account_id
    WHERE jm.voucher_no = 'BRV-2026-0001'
      AND coa.account_code = '1100-03'
      AND d.debit_amount = 275000.00
    LIMIT 1
)
SET s.is_reconciled = 1,
    s.reconciled_at = NOW(),
    s.matched_detail_id = jd.detail_id
WHERE s.statement_id = @stmt2;

UPDATE acc_journal_details jd
SET jd.is_reconciled = 1,
    jd.reconciled_at = NOW(),
    jd.matched_statement_id = @stmt2,
    jd.reconciliation_note = 'Auto matched with bank statement TXN-BRV-001'
WHERE jd.detail_id = (
    SELECT t.detail_id FROM (
        SELECT d.detail_id
        FROM acc_journal_details d
        JOIN acc_journal_masters jm ON jm.journal_id = d.journal_id
        JOIN acc_chart_of_accounts coa ON coa.account_id = d.account_id
        WHERE jm.voucher_no = 'BRV-2026-0001'
          AND coa.account_code = '1100-03'
          AND d.debit_amount = 275000.00
        LIMIT 1
    ) AS t
);

-- Statement line 3: unmatched bank charge entry
INSERT INTO acc_bank_statement_entries
(bank_account_id, statement_date, reference_no, description, debit_amount, credit_amount, closing_balance, is_reconciled, reconciled_at, matched_detail_id, created_at, updated_at)
SELECT ba.bank_account_id, '2026-05-20', 'TXN-CHG-001', 'Bank charge and SMS alert fee', 0.00, 1250.00, 2153750.00, 0, NULL, NULL, NOW(), NOW()
FROM acc_bank_accounts ba
WHERE ba.account_number = '0109012345678';

-- Statement line 4: utility payment from foreign account (matches BPV-2026-0002)
INSERT INTO acc_bank_statement_entries
(bank_account_id, statement_date, reference_no, description, debit_amount, credit_amount, closing_balance, is_reconciled, reconciled_at, matched_detail_id, created_at, updated_at)
SELECT ba.bank_account_id, '2026-06-06', 'TXN-BPV-002', 'Utility payment through EBL account', 0.00, 42000.00, 908000.00, 0, NULL, NULL, NOW(), NOW()
FROM acc_bank_accounts ba
WHERE ba.account_number = '0207009876543';

SET @stmt4 := LAST_INSERT_ID();

UPDATE acc_bank_statement_entries s
JOIN acc_journal_details jd ON jd.detail_id = (
    SELECT d.detail_id
    FROM acc_journal_details d
    JOIN acc_journal_masters jm ON jm.journal_id = d.journal_id
    JOIN acc_chart_of_accounts coa ON coa.account_id = d.account_id
    WHERE jm.voucher_no = 'BPV-2026-0002'
      AND coa.account_code = '1100-04'
      AND d.credit_amount = 42000.00
    LIMIT 1
)
SET s.is_reconciled = 1,
    s.reconciled_at = NOW(),
    s.matched_detail_id = jd.detail_id
WHERE s.statement_id = @stmt4;

UPDATE acc_journal_details jd
SET jd.is_reconciled = 1,
    jd.reconciled_at = NOW(),
    jd.matched_statement_id = @stmt4,
    jd.reconciliation_note = 'Auto matched with bank statement TXN-BPV-002'
WHERE jd.detail_id = (
    SELECT t.detail_id FROM (
        SELECT d.detail_id
        FROM acc_journal_details d
        JOIN acc_journal_masters jm ON jm.journal_id = d.journal_id
        JOIN acc_chart_of_accounts coa ON coa.account_id = d.account_id
        WHERE jm.voucher_no = 'BPV-2026-0002'
          AND coa.account_code = '1100-04'
          AND d.credit_amount = 42000.00
        LIMIT 1
    ) AS t
);

-- Statement line 5: buyer collection (unmatched pending)
INSERT INTO acc_bank_statement_entries
(bank_account_id, statement_date, reference_no, description, debit_amount, credit_amount, closing_balance, is_reconciled, reconciled_at, matched_detail_id, created_at, updated_at)
SELECT ba.bank_account_id, '2026-06-08', 'TXN-BRV-002', 'Second export remittance received', 190000.00, 0.00, 1098000.00, 0, NULL, NULL, NOW(), NOW()
FROM acc_bank_accounts ba
WHERE ba.account_number = '0207009876543';

-- Statement line 6: direct cash deposit
INSERT INTO acc_bank_statement_entries
(bank_account_id, statement_date, reference_no, description, debit_amount, credit_amount, closing_balance, is_reconciled, reconciled_at, matched_detail_id, created_at, updated_at)
SELECT ba.bank_account_id, '2026-06-09', 'TXN-DEP-001', 'Cash deposit to bank account', 50000.00, 0.00, 1148000.00, 0, NULL, NULL, NOW(), NOW()
FROM acc_bank_accounts ba
WHERE ba.account_number = '0207009876543';

COMMIT;
