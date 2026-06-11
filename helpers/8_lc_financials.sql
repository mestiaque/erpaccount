-- ==========================================
-- Demo Data: LC Financials
-- Table: acc_lc_financials
-- ==========================================

START TRANSACTION;

DELETE FROM acc_lc_financials
WHERE lc_id_reference IN (30001, 30002, 30003, 30004);

INSERT INTO acc_lc_financials
(lc_type, lc_id_reference, total_lc_value, currency, exchange_rate, bank_margin_percentage,
 bank_margin_limit, bank_margin_used, bank_commission_paid, acceptance_cost_paid,
 outstanding_liability, customs_clearing_cost, freight_cost, posting_status, created_at, updated_at)
VALUES
('Master_LC', 30001, 850000.00, 'USD', 118.7500, 10.00,
 10000000.00, 5200000.00, 85000.00, 42000.00,
 3150000.00, 120000.00, 185000.00, 'Active', NOW(), NOW()),

('Back_To_Back_LC', 30002, 280000.00, 'USD', 118.7500, 15.00,
 4500000.00, 2000000.00, 42000.00, 18500.00,
 1250000.00, 65000.00, 96000.00, 'Closed', NOW(), NOW());

INSERT INTO acc_lc_financials
(lc_type, lc_id_reference, total_lc_value, currency, exchange_rate, bank_margin_percentage,
 bank_margin_limit, bank_margin_used, bank_commission_paid, acceptance_cost_paid,
 outstanding_liability, customs_clearing_cost, freight_cost, posting_status, created_at, updated_at)
VALUES
('Master_LC', 30003, 1200000.00, 'USD', 119.2500, 12.50,
 16000000.00, 9100000.00, 132000.00, 56000.00,
 4080000.00, 210000.00, 265000.00, 'Active', NOW(), NOW()),

('Back_To_Back_LC', 30004, 460000.00, 'USD', 119.2500, 14.00,
 7000000.00, 3650000.00, 72000.00, 29000.00,
 1880000.00, 93000.00, 145000.00, 'Active', NOW(), NOW());

COMMIT;
