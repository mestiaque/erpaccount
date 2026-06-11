-- ==========================================
-- Demo Data: Tax Rates & Financial Periods
-- Tables: acc_tax_rates, acc_financial_periods
-- Prerequisite: 1_chart_of_accounts.sql
-- ==========================================

START TRANSACTION;

DELETE FROM acc_financial_periods
WHERE period_name IN ('FY 2023-2024', 'FY 2024-2025', 'FY 2025-2026', 'FY 2026-2027', 'FY 2027-2028');

DELETE FROM acc_tax_rates
WHERE tax_name IN ('VAT 15%', 'Source Tax 2%', 'AIT 0.50%', 'Advance Tax 1%', 'Service VAT 5%');

-- Financial periods
INSERT INTO acc_financial_periods
(period_name, start_date, end_date, is_closed, created_at, updated_at)
VALUES
('FY 2023-2024', '2023-07-01', '2024-06-30', 1, NOW(), NOW()),
('FY 2024-2025', '2024-07-01', '2025-06-30', 1, NOW(), NOW()),
('FY 2025-2026', '2025-07-01', '2026-06-30', 0, NOW(), NOW()),
('FY 2026-2027', '2026-07-01', '2027-06-30', 0, NOW(), NOW()),
('FY 2027-2028', '2027-07-01', '2028-06-30', 0, NOW(), NOW());

-- Tax rates (ledger mapped to existing liability accounts for demo)
INSERT INTO acc_tax_rates
(tax_name, percentage, ledger_account_id, is_active, created_at, updated_at)
SELECT 'VAT 15%', 15.00, coa.account_id, 1, NOW(), NOW()
FROM acc_chart_of_accounts coa
WHERE coa.account_code = '2300-03';

INSERT INTO acc_tax_rates
(tax_name, percentage, ledger_account_id, is_active, created_at, updated_at)
SELECT 'Source Tax 2%', 2.00, coa.account_id, 1, NOW(), NOW()
FROM acc_chart_of_accounts coa
WHERE coa.account_code = '2100-02';

INSERT INTO acc_tax_rates
(tax_name, percentage, ledger_account_id, is_active, created_at, updated_at)
SELECT 'AIT 0.50%', 0.50, coa.account_id, 1, NOW(), NOW()
FROM acc_chart_of_accounts coa
WHERE coa.account_code = '2100-03';

INSERT INTO acc_tax_rates
(tax_name, percentage, ledger_account_id, is_active, created_at, updated_at)
SELECT 'Advance Tax 1%', 1.00, coa.account_id, 1, NOW(), NOW()
FROM acc_chart_of_accounts coa
WHERE coa.account_code = '2300-01';

INSERT INTO acc_tax_rates
(tax_name, percentage, ledger_account_id, is_active, created_at, updated_at)
SELECT 'Service VAT 5%', 5.00, coa.account_id, 1, NOW(), NOW()
FROM acc_chart_of_accounts coa
WHERE coa.account_code = '2300-03';

COMMIT;
