-- ==========================================
-- Demo Data: Payroll Integration Batches
-- Table: acc_payroll_integration_batches
-- Note: payroll_month must be unique in this schema
-- ==========================================

START TRANSACTION;

DELETE FROM acc_payroll_integration_batches
WHERE payroll_month IN ('2026-04', '2026-05', '2026-06', '2026-07');

INSERT INTO acc_payroll_integration_batches
(summary_label, payroll_month, payroll_year,
 staff_basic, staff_allowances, staff_pf_deductions, staff_net_payable,
 factory_piece_rate_earnings, factory_overtime_amount, factory_net_payable,
 total_basic, total_allowances, total_overtime, total_pf_deduction, total_advance_adjusted, net_payable,
 posting_status, journal_id, payload, posted_at, created_at, updated_at)
VALUES
(
    'April Payroll Summary', '2026-04', '2026',
    480000.00, 135000.00, 42000.00, 573000.00,
    295000.00, 54000.00, 349000.00,
    775000.00, 189000.00, 54000.00, 42000.00, 25000.00, 951000.00,
    'Posted',
    (SELECT journal_id FROM acc_journal_masters WHERE voucher_no = 'JV-2026-0003' LIMIT 1),
    JSON_OBJECT('staff_count', 62, 'factory_workers', 198, 'currency', 'BDT'),
    NOW(), NOW(), NOW()
),
(
    'May Payroll Summary', '2026-05', '2026',
    500000.00, 142000.00, 45000.00, 597000.00,
    305000.00, 61000.00, 366000.00,
    805000.00, 203000.00, 61000.00, 45000.00, 27000.00, 997000.00,
    'Pending Review',
    NULL,
    JSON_OBJECT('staff_count', 64, 'factory_workers', 205, 'currency', 'BDT'),
    NULL, NOW(), NOW()
),
(
    'June Payroll Summary', '2026-06', '2026',
    512000.00, 148000.00, 46200.00, 613800.00,
    319000.00, 62500.00, 381500.00,
    831000.00, 210500.00, 62500.00, 46200.00, 28500.00, 1029300.00,
    'Pending Review',
    NULL,
    JSON_OBJECT('staff_count', 65, 'factory_workers', 212, 'currency', 'BDT'),
    NULL, NOW(), NOW()
),
(
    'July Payroll Summary', '2026-07', '2026',
    525000.00, 152000.00, 47000.00, 630000.00,
    325000.00, 64800.00, 389800.00,
    850000.00, 216800.00, 64800.00, 47000.00, 29200.00, 1055400.00,
    'Pending Review',
    NULL,
    JSON_OBJECT('staff_count', 67, 'factory_workers', 218, 'currency', 'BDT'),
    NULL, NOW(), NOW()
);

COMMIT;
