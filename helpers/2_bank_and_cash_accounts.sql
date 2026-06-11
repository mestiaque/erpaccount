-- ==========================================
-- Demo Data: Bank & Cash Accounts
-- Table: acc_bank_accounts
-- Prerequisite: 1_chart_of_accounts.sql
-- ==========================================

START TRANSACTION;

DELETE FROM acc_bank_accounts
WHERE account_number IN (
	'0109012345678',
	'0207009876543',
	'0903011122334',
	'3400122233344',
	'7700455566677'
);

INSERT INTO acc_bank_accounts
(account_id, bank_name, branch_name, account_number, account_type, swift_code, is_active, created_at, updated_at)
SELECT coa.account_id, 'BRAC Bank PLC', 'Gulshan Corporate Branch', '0109012345678', 'Current', 'BRAKBDDH', 1, NOW(), NOW()
FROM acc_chart_of_accounts coa
WHERE coa.account_code = '1100-03';

INSERT INTO acc_bank_accounts
(account_id, bank_name, branch_name, account_number, account_type, swift_code, is_active, created_at, updated_at)
SELECT coa.account_id, 'Eastern Bank PLC', 'Principal Branch', '0207009876543', 'Foreign Currency', 'EBLDBDDH', 1, NOW(), NOW()
FROM acc_chart_of_accounts coa
WHERE coa.account_code = '1100-04';

INSERT INTO acc_bank_accounts
(account_id, bank_name, branch_name, account_number, account_type, swift_code, is_active, created_at, updated_at)
SELECT coa.account_id, 'Dutch-Bangla Bank PLC', 'Banani Branch', '0903011122334', 'Current', 'DBBLBDDH', 1, NOW(), NOW()
FROM acc_chart_of_accounts coa
WHERE coa.account_code = '1100-03'
LIMIT 1;

INSERT INTO acc_bank_accounts
(account_id, bank_name, branch_name, account_number, account_type, swift_code, is_active, created_at, updated_at)
SELECT coa.account_id, 'City Bank PLC', 'Uttara Branch', '3400122233344', 'Current', 'CIBLBDDH', 1, NOW(), NOW()
FROM acc_chart_of_accounts coa
WHERE coa.account_code = '1100-03'
LIMIT 1;

INSERT INTO acc_bank_accounts
(account_id, bank_name, branch_name, account_number, account_type, swift_code, is_active, created_at, updated_at)
SELECT coa.account_id, 'HSBC Bangladesh', 'Motijheel Branch', '7700455566677', 'Foreign Currency', 'HSBCBDDH', 1, NOW(), NOW()
FROM acc_chart_of_accounts coa
WHERE coa.account_code = '1100-04'
LIMIT 1;

COMMIT;
