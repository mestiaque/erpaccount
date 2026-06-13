<?php

use Illuminate\Support\Facades\Route;
use ME\Erpaccount\Http\Controllers\BankAccountController;
use ME\Erpaccount\Http\Controllers\BankReconciliationController;
use ME\Erpaccount\Http\Controllers\CashBankVoucherController;
use ME\Erpaccount\Http\Controllers\AccountsTutorialController;
use ME\Erpaccount\Http\Controllers\ChartOfAccountController;
use ME\Erpaccount\Http\Controllers\CommercialLcTrackerController;
use ME\Erpaccount\Http\Controllers\CostCenterController;
use ME\Erpaccount\Http\Controllers\DashboardController;
use ME\Erpaccount\Http\Controllers\FinancialPeriodController;
use ME\Erpaccount\Http\Controllers\FinancialReportController;
use ME\Erpaccount\Http\Controllers\ReportsHubController;
use ME\Erpaccount\Http\Controllers\InventoryPostingBridgeController;
use ME\Erpaccount\Http\Controllers\JournalVoucherController;
use ME\Erpaccount\Http\Controllers\PartyLedgerController;
use ME\Erpaccount\Http\Controllers\PayrollIntegrationController;
use ME\Erpaccount\Http\Controllers\StyleProfitabilityController;
use ME\Erpaccount\Http\Controllers\TaxRateController;
use ME\Erpaccount\Http\Controllers\VoucherRegisterController;
use ME\Erpaccount\Http\Controllers\ApprovalQueueController;

$webMiddleware = config('erpaccount.route_middleware', ['web']);

Route::middleware($webMiddleware)->prefix('erpaccount')->as('erpaccount.')->group(function () {
    Route::get('/', function () {
        return redirect()->route('erpaccount.executive-dashboard.index');
    })->name('dashboard');

    Route::resource('chart-of-accounts', ChartOfAccountController::class)
        ->parameters(['chart-of-accounts' => 'chartOfAccount'])
        ->except(['create', 'show', 'edit']);

    Route::resource('bank-accounts', BankAccountController::class)
        ->parameters(['bank-accounts' => 'bankAccount'])
        ->except(['create', 'show', 'edit']);

    Route::resource('cost-centers', CostCenterController::class)
        ->parameters(['cost-centers' => 'costCenter'])
        ->except(['create', 'show', 'edit']);
    Route::post('cost-centers/types', [CostCenterController::class, 'storeType'])
        ->name('cost-centers.types.store');
    Route::put('cost-centers/types/{costCenterType}', [CostCenterController::class, 'updateType'])
        ->name('cost-centers.types.update');
    Route::delete('cost-centers/types/{costCenterType}', [CostCenterController::class, 'destroyType'])
        ->name('cost-centers.types.destroy');

    Route::resource('tax-rates', TaxRateController::class)
        ->parameters(['tax-rates' => 'taxRate'])
        ->only(['index', 'store', 'update']);

    Route::resource('financial-periods', FinancialPeriodController::class)
        ->parameters(['financial-periods' => 'financialPeriod'])
        ->only(['index', 'store', 'update']);

    Route::get('journal-vouchers/party-options', [JournalVoucherController::class, 'partyOptions'])
        ->name('journal-vouchers.party-options');
    Route::resource('journal-vouchers', JournalVoucherController::class)
        ->only(['index', 'store']);

    Route::get('voucher-register', [VoucherRegisterController::class, 'index'])->name('voucher-register.index');
    Route::get('voucher-register/{journalMaster}', [VoucherRegisterController::class, 'show'])
        ->name('voucher-register.show');
    Route::patch('voucher-register/{journalMaster}/void', [VoucherRegisterController::class, 'void'])
        ->name('voucher-register.void');

    Route::get('cash-bank-vouchers', [CashBankVoucherController::class, 'index'])
        ->name('cash-bank-vouchers.index');
    Route::post('cash-bank-vouchers/receipt', [CashBankVoucherController::class, 'storeReceipt'])
        ->name('cash-bank-vouchers.receipt.store');
    Route::post('cash-bank-vouchers/payment', [CashBankVoucherController::class, 'storePayment'])
        ->name('cash-bank-vouchers.payment.store');

    Route::get('bank-reconciliation', [BankReconciliationController::class, 'index'])
        ->name('bank-reconciliation.index');
    Route::post('bank-reconciliation/upload', [BankReconciliationController::class, 'upload'])
        ->name('bank-reconciliation.upload');
    Route::post('bank-reconciliation/match', [BankReconciliationController::class, 'match'])
        ->name('bank-reconciliation.match');

    Route::get('manual-inventory', [InventoryPostingBridgeController::class, 'index'])->name('manual-inventory.index');
    Route::post('manual-inventory', [InventoryPostingBridgeController::class, 'store'])->name('manual-inventory.store');

    Route::get('commercial-lc-tracker', [CommercialLcTrackerController::class, 'index'])->name('commercial-lc-tracker.index');
    Route::post('commercial-lc-tracker/sync', [CommercialLcTrackerController::class, 'sync'])->name('commercial-lc-tracker.sync');

    Route::get('manual-payroll', [PayrollIntegrationController::class, 'index'])->name('manual-payroll.index');
    Route::post('manual-payroll', [PayrollIntegrationController::class, 'store'])->name('manual-payroll.store');

    Route::get('approvals', [ApprovalQueueController::class, 'index'])->name('approvals.index');
    Route::post('approvals/approve-inventory/{logId}', [ApprovalQueueController::class, 'approveInventory'])->name('approvals.approve-inventory');
    Route::post('approvals/approve-payroll/{batchId}', [ApprovalQueueController::class, 'approvePayroll'])->name('approvals.approve-payroll');

    Route::get('executive-dashboard', [DashboardController::class, 'index'])->name('executive-dashboard.index');
    Route::get('style-profitability', [StyleProfitabilityController::class, 'index'])->name('style-profitability.index');

    Route::get('reports', [ReportsHubController::class, 'index'])->name('reports.index');
    Route::get('reports/{reportSlug}', [ReportsHubController::class, 'show'])->name('reports.show');
    Route::get('reports/{reportSlug}/export-excel', [ReportsHubController::class, 'exportExcel'])->name('reports.export-excel');
    Route::get('reports/{reportSlug}/export-pdf', [ReportsHubController::class, 'exportPdf'])->name('reports.export-pdf');

    Route::get('financial-reports', [FinancialReportController::class, 'index'])->name('financial-reports.index');
    Route::get('financial-reports/export-excel', [FinancialReportController::class, 'exportExcel'])->name('financial-reports.export-excel');
    Route::get('financial-reports/print', [FinancialReportController::class, 'printFriendly'])->name('financial-reports.print');

    Route::get('accounts-tutorial', [AccountsTutorialController::class, 'index'])
        ->name('accounts-tutorial.index');

    Route::get('party-ledger', [PartyLedgerController::class, 'index'])->name('party-ledger.index');
    Route::get('party-ledger/export-excel', [PartyLedgerController::class, 'exportExcel'])->name('party-ledger.export-excel');
    Route::get('party-ledger/print', [PartyLedgerController::class, 'printFriendly'])->name('party-ledger.print');
});

