# Erpaccount Package

Laravel accounts module for garment/ERP operations: chart of accounts, journal vouchers, cash/bank, bank reconciliation, financial reports, and executive dashboard.

## Installation

```bash
composer require mestiaque/erpaccount
php artisan migrate
```

### Seed Chart of Accounts (first deploy)

Run the SQL seed against your database after migrations:

```bash
mysql -u USER -p DATABASE < vendor/mestiaque/erpaccount/helpers/chart_of_accounts.sql
```

Or copy `helpers/chart_of_accounts.sql` into your project and execute via your DB tool.

### Publish config (optional)

```bash
php artisan vendor:publish --tag=erpaccount-config
```

Adjust middleware in `config/erpaccount.php` if your app uses different auth guards:

```php
'route_middleware' => ['web', 'auth'],
'api_route_middleware' => ['api', 'auth:sanctum'],
```

### Permissions

Register keys from `config/erpaccount.permissions` (or package `src/Config/permission.php`) in your host app's role system:

| Key | Purpose |
|-----|---------|
| `erpaccount.view` | Browse vouchers and registers |
| `erpaccount.post` | Post journals and cash/bank vouchers |
| `erpaccount.void` | Void vouchers |
| `erpaccount.reports` | Dashboard and financial reports |
| `erpaccount.config` | COA, bank accounts, tax, periods |

## Routes

| Area | Web prefix |
|------|------------|
| Dashboard (home) | `/erpaccount/executive-dashboard` |
| Chart of Accounts | `/erpaccount/chart-of-accounts` |
| Journal Vouchers | `/erpaccount/journal-vouchers` |
| Voucher Register | `/erpaccount/voucher-register` |
| Cash & Bank | `/erpaccount/cash-bank-vouchers` |
| Bank Reconciliation | `/erpaccount/bank-reconciliation` |
| Reports Center (83 reports) | `/erpaccount/reports` |
| Single report + PDF/Excel | `/erpaccount/reports/{slug}` |
| Party Ledger | `/erpaccount/party-ledger` |

API base: `/api/erpaccount/v1/...`

## Delivery checklist

1. Run migrations (includes `is_voided` on journal masters).
2. Seed COA from `helpers/chart_of_accounts.sql`.
3. Create at least one **open** financial period covering today's date.
4. Map bank accounts under **Bank & Cash Accounts**.
5. Assign `erpaccount.*` permissions to finance users.
6. Post a test JV, confirm it appears in **Voucher Register** and **Trial Balance**.
7. Close a past period and verify new postings on that date are blocked.

## Features (delivery build)

- Double-entry journal, receipt, and payment vouchers
- Closed financial period enforcement on all posting dates
- Voucher register with search, detail view, and void (excluded from reports)
- **83 Garments Reports** — Core financial, VAT/TAX, Purchase, Export, LC, Production, Payroll, Inventory, Bank/Cash, Management (PDF + Excel on every report)
- Trial Balance, P&L, Balance Sheet, Party Ledger, Executive Dashboard
- Bank reconciliation worksheet
- Sidebar permissions wired to `erpaccount.*` keys
- Manual Inventory, Manual Payroll, and Commercial LC Tracker bridge screens listed in the sidebar
