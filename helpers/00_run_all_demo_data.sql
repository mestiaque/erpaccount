-- ==========================================
-- Master Runner: All ERP Accounting Demo Data
-- Usage (MySQL CLI):
--   mysql -u root -padmin erpanrf_erp < helpers/00_run_all_demo_data.sql
-- ==========================================

-- Core master data
SOURCE helpers/1_chart_of_accounts.sql;
SOURCE helpers/2_bank_and_cash_accounts.sql;
SOURCE helpers/3_tax_and_financial_period.sql;
SOURCE helpers/6_cost_centers.sql;

-- Voucher flows
SOURCE helpers/4_universal_voucher.sql;
SOURCE helpers/5_cash_and_bank_voucher.sql;

-- Reconciliation and integrations
SOURCE helpers/7_bank_statement_and_reconciliation.sql;
SOURCE helpers/8_lc_financials.sql;
SOURCE helpers/9_inventory_posting_logs.sql;
SOURCE helpers/10_payroll_integration_batches.sql;

-- ==========================================
-- Done
-- ==========================================
