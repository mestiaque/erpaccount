<?php

use Illuminate\Support\Facades\Route;
use ME\Erpaccount\Http\Controllers\BankAccountController;
use ME\Erpaccount\Http\Controllers\BankReconciliationController;
use ME\Erpaccount\Http\Controllers\CashBankVoucherController;
use ME\Erpaccount\Http\Controllers\ChartOfAccountController;
use ME\Erpaccount\Http\Controllers\CommercialLcTrackerController;
use ME\Erpaccount\Http\Controllers\FinancialPeriodController;
use ME\Erpaccount\Http\Controllers\FinancialReportController;
use ME\Erpaccount\Http\Controllers\ReportsHubController;
use ME\Erpaccount\Http\Controllers\JournalVoucherController;
use ME\Erpaccount\Http\Controllers\PartyLedgerController;
use ME\Erpaccount\Http\Controllers\TaxRateController;
use ME\Erpaccount\Http\Controllers\VoucherRegisterController;

$apiMiddleware = config('erpaccount.api_route_middleware', ['api', 'auth:sanctum']);

Route::prefix('api/erpaccount/v1')->as('erpaccount.api.')->middleware($apiMiddleware)->group(function () {
    Route::apiResource('chart-of-accounts', ChartOfAccountController::class)
        ->parameters(['chart-of-accounts' => 'chartOfAccount'])
        ->only(['index', 'store', 'update', 'destroy']);

    Route::apiResource('bank-accounts', BankAccountController::class)
        ->parameters(['bank-accounts' => 'bankAccount'])
        ->only(['index', 'store', 'update', 'destroy']);

    Route::apiResource('tax-rates', TaxRateController::class)
        ->parameters(['tax-rates' => 'taxRate'])
        ->only(['index', 'store', 'update']);

    Route::apiResource('financial-periods', FinancialPeriodController::class)
        ->parameters(['financial-periods' => 'financialPeriod'])
        ->only(['index', 'store', 'update']);

    Route::get('journal-vouchers/party-options', [JournalVoucherController::class, 'partyOptions'])
        ->name('journal-vouchers.party-options');
    Route::apiResource('journal-vouchers', JournalVoucherController::class)
        ->only(['index', 'store']);

    Route::get('voucher-register', [VoucherRegisterController::class, 'index'])->name('voucher-register.index');
    Route::get('voucher-register/{journalMaster}', [VoucherRegisterController::class, 'show'])
        ->name('voucher-register.show');
    Route::patch('voucher-register/{journalMaster}/void', [VoucherRegisterController::class, 'void'])
        ->name('voucher-register.void');

    Route::get('party-ledger', [PartyLedgerController::class, 'index'])
        ->name('party-ledger.index');

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

    Route::get('commercial-lc-tracker', [CommercialLcTrackerController::class, 'index'])->name('commercial-lc-tracker.index');
    Route::post('commercial-lc-tracker/sync', [CommercialLcTrackerController::class, 'sync'])->name('commercial-lc-tracker.sync');

    Route::get('reports', [ReportsHubController::class, 'index'])->name('reports.index');
    Route::get('reports/{reportSlug}', [ReportsHubController::class, 'show'])->name('reports.show');
    Route::get('reports/{reportSlug}/export-excel', [ReportsHubController::class, 'exportExcel'])->name('reports.export-excel');
    Route::get('reports/{reportSlug}/export-pdf', [ReportsHubController::class, 'exportPdf'])->name('reports.export-pdf');

    Route::get('financial-reports', [FinancialReportController::class, 'index'])->name('financial-reports.index');
    Route::get('financial-reports/export-excel', [FinancialReportController::class, 'exportExcel'])->name('financial-reports.export-excel');
    Route::get('financial-reports/print', [FinancialReportController::class, 'printFriendly'])->name('financial-reports.print');
});
