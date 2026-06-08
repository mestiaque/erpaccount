-- ==========================================
-- Chart of Accounts (Parent-Child Hierarchy)
-- Compatible with:
-- account_type ENUM('Asset','Liability','Equity','Revenue','Expense')
-- ==========================================

START TRANSACTION;

-- =====================================================
-- 1. ASSET
-- =====================================================

-- Parent Accounts
INSERT INTO acc_chart_of_accounts
(account_code, account_name, parent_id, account_type, is_reconcilable, is_active, created_at, updated_at)
VALUES
('1100', 'Cash & Bank', NULL, 'Asset', 0, 1, NOW(), NOW()),
('1200', 'Accounts Receivable', NULL, 'Asset', 1, 1, NOW(), NOW()),
('1300', 'Inventory / Stock', NULL, 'Asset', 0, 1, NOW(), NOW()),
('1500', 'Fixed Assets', NULL, 'Asset', 0, 1, NOW(), NOW());

-- Child Accounts under Cash & Bank
INSERT INTO acc_chart_of_accounts
(account_code, account_name, parent_id, account_type, is_reconcilable, is_active, created_at, updated_at)
SELECT '1100-01', 'Cash in Hand (Factory Cash)', account_id, 'Asset', 0, 1, NOW(), NOW()
FROM acc_chart_of_accounts WHERE account_code = '1100';

INSERT INTO acc_chart_of_accounts
(account_code, account_name, parent_id, account_type, is_reconcilable, is_active, created_at, updated_at)
SELECT '1100-02', 'Petty Cash (Office Cash)', account_id, 'Asset', 0, 1, NOW(), NOW()
FROM acc_chart_of_accounts WHERE account_code = '1100';

INSERT INTO acc_chart_of_accounts
(account_code, account_name, parent_id, account_type, is_reconcilable, is_active, created_at, updated_at)
SELECT '1100-03', 'Commercial Bank A/C (BDT)', account_id, 'Asset', 1, 1, NOW(), NOW()
FROM acc_chart_of_accounts WHERE account_code = '1100';

INSERT INTO acc_chart_of_accounts
(account_code, account_name, parent_id, account_type, is_reconcilable, is_active, created_at, updated_at)
SELECT '1100-04', 'ERQ Account (Foreign Currency)', account_id, 'Asset', 1, 1, NOW(), NOW()
FROM acc_chart_of_accounts WHERE account_code = '1100';

-- Child Accounts under Accounts Receivable
INSERT INTO acc_chart_of_accounts
(account_code, account_name, parent_id, account_type, is_reconcilable, is_active, created_at, updated_at)
SELECT '1200-01', 'Buyer Receivable (Foreign)', account_id, 'Asset', 1, 1, NOW(), NOW()
FROM acc_chart_of_accounts WHERE account_code = '1200';

INSERT INTO acc_chart_of_accounts
(account_code, account_name, parent_id, account_type, is_reconcilable, is_active, created_at, updated_at)
SELECT '1200-02', 'Local Buyer Receivable', account_id, 'Asset', 1, 1, NOW(), NOW()
FROM acc_chart_of_accounts WHERE account_code = '1200';

-- Child Accounts under Inventory / Stock
INSERT INTO acc_chart_of_accounts
(account_code, account_name, parent_id, account_type, is_reconcilable, is_active, created_at, updated_at)
SELECT '1300-01', 'Fabric Stock', account_id, 'Asset', 0, 1, NOW(), NOW()
FROM acc_chart_of_accounts WHERE account_code = '1300';

INSERT INTO acc_chart_of_accounts
(account_code, account_name, parent_id, account_type, is_reconcilable, is_active, created_at, updated_at)
SELECT '1300-02', 'Yarn Stock', account_id, 'Asset', 0, 1, NOW(), NOW()
FROM acc_chart_of_accounts WHERE account_code = '1300';

INSERT INTO acc_chart_of_accounts
(account_code, account_name, parent_id, account_type, is_reconcilable, is_active, created_at, updated_at)
SELECT '1300-03', 'Trims & Accessories Stock', account_id, 'Asset', 0, 1, NOW(), NOW()
FROM acc_chart_of_accounts WHERE account_code = '1300';

INSERT INTO acc_chart_of_accounts
(account_code, account_name, parent_id, account_type, is_reconcilable, is_active, created_at, updated_at)
SELECT '1300-04', 'Work in Progress (WIP)', account_id, 'Asset', 0, 1, NOW(), NOW()
FROM acc_chart_of_accounts WHERE account_code = '1300';

INSERT INTO acc_chart_of_accounts
(account_code, account_name, parent_id, account_type, is_reconcilable, is_active, created_at, updated_at)
SELECT '1300-05', 'Finished Goods Stock', account_id, 'Asset', 0, 1, NOW(), NOW()
FROM acc_chart_of_accounts WHERE account_code = '1300';

-- Child Accounts under Fixed Assets
INSERT INTO acc_chart_of_accounts
(account_code, account_name, parent_id, account_type, is_reconcilable, is_active, created_at, updated_at)
SELECT '1500-01', 'Factory Land & Building', account_id, 'Asset', 0, 1, NOW(), NOW()
FROM acc_chart_of_accounts WHERE account_code = '1500';

INSERT INTO acc_chart_of_accounts
(account_code, account_name, parent_id, account_type, is_reconcilable, is_active, created_at, updated_at)
SELECT '1500-02', 'Sewing & Cutting Machinery', account_id, 'Asset', 0, 1, NOW(), NOW()
FROM acc_chart_of_accounts WHERE account_code = '1500';

INSERT INTO acc_chart_of_accounts
(account_code, account_name, parent_id, account_type, is_reconcilable, is_active, created_at, updated_at)
SELECT '1500-03', 'Generator & Boiler Plant', account_id, 'Asset', 0, 1, NOW(), NOW()
FROM acc_chart_of_accounts WHERE account_code = '1500';

INSERT INTO acc_chart_of_accounts
(account_code, account_name, parent_id, account_type, is_reconcilable, is_active, created_at, updated_at)
SELECT '1500-04', 'Office Vehicles', account_id, 'Asset', 0, 1, NOW(), NOW()
FROM acc_chart_of_accounts WHERE account_code = '1500';


-- =====================================================
-- 2. LIABILITY
-- =====================================================

-- Parent Accounts
INSERT INTO acc_chart_of_accounts
(account_code, account_name, parent_id, account_type, is_reconcilable, is_active, created_at, updated_at)
VALUES
('2100', 'Accounts Payable', NULL, 'Liability', 1, 1, NOW(), NOW()),
('2200', 'Bank Liabilities & LC', NULL, 'Liability', 1, 1, NOW(), NOW()),
('2300', 'Outstanding Expenses', NULL, 'Liability', 0, 1, NOW(), NOW()),
('2500', 'Long-term Bank Loan', NULL, 'Liability', 1, 1, NOW(), NOW());

-- Child Accounts under Accounts Payable
INSERT INTO acc_chart_of_accounts
(account_code, account_name, parent_id, account_type, is_reconcilable, is_active, created_at, updated_at)
SELECT '2100-01', 'Yarn/Fabric Suppliers Payable', account_id, 'Liability', 1, 1, NOW(), NOW()
FROM acc_chart_of_accounts WHERE account_code = '2100';

INSERT INTO acc_chart_of_accounts
(account_code, account_name, parent_id, account_type, is_reconcilable, is_active, created_at, updated_at)
SELECT '2100-02', 'Accessories Suppliers Payable', account_id, 'Liability', 1, 1, NOW(), NOW()
FROM acc_chart_of_accounts WHERE account_code = '2100';

INSERT INTO acc_chart_of_accounts
(account_code, account_name, parent_id, account_type, is_reconcilable, is_active, created_at, updated_at)
SELECT '2100-03', 'Sub-contractors Payable', account_id, 'Liability', 1, 1, NOW(), NOW()
FROM acc_chart_of_accounts WHERE account_code = '2100';

-- Child Accounts under Bank Liabilities & LC
INSERT INTO acc_chart_of_accounts
(account_code, account_name, parent_id, account_type, is_reconcilable, is_active, created_at, updated_at)
SELECT '2200-01', 'Back-to-Back LC Liability', account_id, 'Liability', 1, 1, NOW(), NOW()
FROM acc_chart_of_accounts WHERE account_code = '2200';

INSERT INTO acc_chart_of_accounts
(account_code, account_name, parent_id, account_type, is_reconcilable, is_active, created_at, updated_at)
SELECT '2200-02', 'Packing Credit (PC) Loan', account_id, 'Liability', 1, 1, NOW(), NOW()
FROM acc_chart_of_accounts WHERE account_code = '2200';

-- Child Accounts under Outstanding Expenses
INSERT INTO acc_chart_of_accounts
(account_code, account_name, parent_id, account_type, is_reconcilable, is_active, created_at, updated_at)
SELECT '2300-01', 'Outstanding Factory Wages', account_id, 'Liability', 0, 1, NOW(), NOW()
FROM acc_chart_of_accounts WHERE account_code = '2300';

INSERT INTO acc_chart_of_accounts
(account_code, account_name, parent_id, account_type, is_reconcilable, is_active, created_at, updated_at)
SELECT '2300-02', 'Outstanding Staff Salary', account_id, 'Liability', 0, 1, NOW(), NOW()
FROM acc_chart_of_accounts WHERE account_code = '2300';

INSERT INTO acc_chart_of_accounts
(account_code, account_name, parent_id, account_type, is_reconcilable, is_active, created_at, updated_at)
SELECT '2300-03', 'Accrued Utilities (Gas/Power)', account_id, 'Liability', 0, 1, NOW(), NOW()
FROM acc_chart_of_accounts WHERE account_code = '2300';


-- =====================================================
-- 3. EQUITY
-- =====================================================

INSERT INTO acc_chart_of_accounts
(account_code, account_name, parent_id, account_type, is_reconcilable, is_active, created_at, updated_at)
VALUES
('3100', 'Shareholders Equity', NULL, 'Equity', 0, 1, NOW(), NOW()),
('3200', 'Retained Earnings', NULL, 'Equity', 0, 1, NOW(), NOW());

INSERT INTO acc_chart_of_accounts
(account_code, account_name, parent_id, account_type, is_reconcilable, is_active, created_at, updated_at)
SELECT '3100-01', 'Managing Director Capital', account_id, 'Equity', 0, 1, NOW(), NOW()
FROM acc_chart_of_accounts WHERE account_code = '3100';

INSERT INTO acc_chart_of_accounts
(account_code, account_name, parent_id, account_type, is_reconcilable, is_active, created_at, updated_at)
SELECT '3100-02', 'Director Capital', account_id, 'Equity', 0, 1, NOW(), NOW()
FROM acc_chart_of_accounts WHERE account_code = '3100';

INSERT INTO acc_chart_of_accounts
(account_code, account_name, parent_id, account_type, is_reconcilable, is_active, created_at, updated_at)
SELECT '3200-01', 'Retained Earnings', account_id, 'Equity', 0, 1, NOW(), NOW()
FROM acc_chart_of_accounts WHERE account_code = '3200';


-- =====================================================
-- 4. REVENUE
-- =====================================================

INSERT INTO acc_chart_of_accounts
(account_code, account_name, parent_id, account_type, is_reconcilable, is_active, created_at, updated_at)
VALUES
('4100', 'Core Sales Revenue', NULL, 'Revenue', 0, 1, NOW(), NOW()),
('4200', 'Other Operational Income', NULL, 'Revenue', 0, 1, NOW(), NOW());

INSERT INTO acc_chart_of_accounts
(account_code, account_name, parent_id, account_type, is_reconcilable, is_active, created_at, updated_at)
SELECT '4100-01', 'Export Sales', account_id, 'Revenue', 0, 1, NOW(), NOW()
FROM acc_chart_of_accounts WHERE account_code = '4100';

INSERT INTO acc_chart_of_accounts
(account_code, account_name, parent_id, account_type, is_reconcilable, is_active, created_at, updated_at)
SELECT '4100-02', 'Local Sales', account_id, 'Revenue', 0, 1, NOW(), NOW()
FROM acc_chart_of_accounts WHERE account_code = '4100';

INSERT INTO acc_chart_of_accounts
(account_code, account_name, parent_id, account_type, is_reconcilable, is_active, created_at, updated_at)
SELECT '4200-01', 'Fabric Waste / Jute Sales', account_id, 'Revenue', 0, 1, NOW(), NOW()
FROM acc_chart_of_accounts WHERE account_code = '4200';

INSERT INTO acc_chart_of_accounts
(account_code, account_name, parent_id, account_type, is_reconcilable, is_active, created_at, updated_at)
SELECT '4200-02', 'Sub-Contracting Income', account_id, 'Revenue', 0, 1, NOW(), NOW()
FROM acc_chart_of_accounts WHERE account_code = '4200';

INSERT INTO acc_chart_of_accounts
(account_code, account_name, parent_id, account_type, is_reconcilable, is_active, created_at, updated_at)
SELECT '4200-03', 'Sample Sales', account_id, 'Revenue', 0, 1, NOW(), NOW();


-- =====================================================
-- 5. EXPENSE
-- =====================================================

-- Parent Accounts
INSERT INTO acc_chart_of_accounts
(account_code, account_name, parent_id, account_type, is_reconcilable, is_active, created_at, updated_at)
VALUES
('5100', 'Cost of Goods Sold (COGS)', NULL, 'Expense', 0, 1, NOW(), NOW()),
('5200', 'Direct Factory Expenses', NULL, 'Expense', 0, 1, NOW(), NOW()),
('6100', 'Administrative Expenses', NULL, 'Expense', 0, 1, NOW(), NOW()),
('6200', 'Financial & Marketing Expenses', NULL, 'Expense', 0, 1, NOW(), NOW());

-- Child Accounts under COGS
INSERT INTO acc_chart_of_accounts
(account_code, account_name, parent_id, account_type, is_reconcilable, is_active, created_at, updated_at)
SELECT '5100-01', 'Fabric Purchase Cost', account_id, 'Expense', 0, 1, NOW(), NOW()
FROM acc_chart_of_accounts WHERE account_code = '5100';

INSERT INTO acc_chart_of_accounts
(account_code, account_name, parent_id, account_type, is_reconcilable, is_active, created_at, updated_at)
SELECT '5100-02', 'Yarn Purchase Cost', account_id, 'Expense', 0, 1, NOW(), NOW()
FROM acc_chart_of_accounts WHERE account_code = '5100';

INSERT INTO acc_chart_of_accounts
(account_code, account_name, parent_id, account_type, is_reconcilable, is_active, created_at, updated_at)
SELECT '5100-03', 'Accessories & Trims Cost', account_id, 'Expense', 0, 1, NOW(), NOW()
FROM acc_chart_of_accounts WHERE account_code = '5100';

INSERT INTO acc_chart_of_accounts
(account_code, account_name, parent_id, account_type, is_reconcilable, is_active, created_at, updated_at)
SELECT '5100-04', 'Dyeing & Printing Cost', account_id, 'Expense', 0, 1, NOW(), NOW()
FROM acc_chart_of_accounts WHERE account_code = '5100';

COMMIT;
