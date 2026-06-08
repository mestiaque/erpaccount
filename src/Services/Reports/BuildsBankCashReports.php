<?php

namespace ME\Erpaccount\Services\Reports;

trait BuildsBankCashReports
{
    protected function bookLines(array $filters, array $keywords): array
    {
        [$start, $end] = $this->query->parsePeriod($filters);
        $ids = $this->query->accountIdsByKeywords($keywords, ['Asset']);

        return [$start, $end, $this->query->journalLines($start, $end, $ids)];
    }

    protected function reportCashBookReport(array $filters): array
    {
        [$start, $end, $lines] = $this->bookLines($filters, ['cash', 'petty']);

        return $this->table('Cash Book Report', "Period: {$start->toDateString()} to {$end->toDateString()}", [
            ['key' => 'journal_date', 'label' => 'Date'],
            ['key' => 'voucher_no', 'label' => 'Voucher'],
            ['key' => 'account_name', 'label' => 'Account'],
            ['key' => 'narration', 'label' => 'Particulars'],
            ['key' => 'debit_amount', 'label' => 'Receipt', 'align' => 'right', 'format' => 'money'],
            ['key' => 'credit_amount', 'label' => 'Payment', 'align' => 'right', 'format' => 'money'],
        ], $lines);
    }

    protected function reportBankBookReport(array $filters): array
    {
        [$start, $end, $lines] = $this->bookLines($filters, ['bank']);

        return $this->table('Bank Book Report', "Period: {$start->toDateString()} to {$end->toDateString()}", [
            ['key' => 'journal_date', 'label' => 'Date'],
            ['key' => 'voucher_no', 'label' => 'Voucher'],
            ['key' => 'account_name', 'label' => 'Bank Account'],
            ['key' => 'narration', 'label' => 'Particulars'],
            ['key' => 'debit_amount', 'label' => 'Debit', 'align' => 'right', 'format' => 'money'],
            ['key' => 'credit_amount', 'label' => 'Credit', 'align' => 'right', 'format' => 'money'],
        ], $lines);
    }

    protected function reportBankReconciliationStatement(array $filters): array
    {
        [, , $asOn] = $this->query->parsePeriod($filters);
        $bankIds = $this->query->accountIdsByKeywords(['bank'], ['Asset']);
        $book = (float) $this->query->accountBalancesAsOn($asOn, $bankIds)->sum('balance');

        $unreconciled = \Illuminate\Support\Facades\DB::table('acc_journal_details as jd')
            ->join('acc_journal_masters as jm', function ($join) {
                \ME\Erpaccount\Support\JournalQueryScopes::activeMasterOnJoin($join);
            })
            ->whereIn('jd.account_id', $bankIds)
            ->where('jd.is_reconciled', false)
            ->whereDate('jm.journal_date', '<=', $asOn->toDateString())
            ->selectRaw('COALESCE(SUM(jd.debit_amount - jd.credit_amount), 0) as amount')
            ->value('amount');

        $rows = collect([
            (object) ['item' => 'Book Balance (Bank Ledger)', 'amount' => round($book, 2)],
            (object) ['item' => 'Unreconciled Items (Net)', 'amount' => round((float) $unreconciled, 2)],
            (object) ['item' => 'Suggested Statement Balance', 'amount' => round($book - (float) $unreconciled, 2)],
        ]);

        return $this->table(
            'Bank Reconciliation Statement (BRS)',
            'As on ' . $asOn->toDateString(),
            [
                ['key' => 'item', 'label' => 'Item'],
                ['key' => 'amount', 'label' => 'Amount', 'align' => 'right', 'format' => 'money'],
            ],
            $rows,
            [],
            ['Upload bank statement in Bank Reconciliation screen for full matching.']
        );
    }

    protected function reportDailyCashPositionReport(array $filters): array
    {
        [, , $asOn] = $this->query->parsePeriod($filters);
        $ids = $this->query->accountIdsByKeywords(['cash', 'bank'], ['Asset']);
        $rows = $this->query->accountBalancesAsOn($asOn, $ids);

        return $this->table('Daily Cash Position Report', 'As on ' . $asOn->toDateString(), [
            ['key' => 'account_name', 'label' => 'Account'],
            ['key' => 'balance', 'label' => 'Balance', 'align' => 'right', 'format' => 'money'],
        ], $rows, ['Total Cash & Bank' => $this->money((float) $rows->sum('balance'))]);
    }

    protected function reportPaymentVoucherReport(array $filters): array
    {
        [$start, $end] = $this->query->parsePeriod($filters);
        $lines = $this->query->journalLines($start, $end, null, null, 'PV');

        return $this->table('Payment Voucher Report', "Period: {$start->toDateString()} to {$end->toDateString()}", [
            ['key' => 'journal_date', 'label' => 'Date'],
            ['key' => 'voucher_no', 'label' => 'PV No'],
            ['key' => 'account_name', 'label' => 'Account'],
            ['key' => 'debit_amount', 'label' => 'Debit', 'align' => 'right', 'format' => 'money'],
            ['key' => 'credit_amount', 'label' => 'Credit', 'align' => 'right', 'format' => 'money'],
        ], $lines);
    }

    protected function reportReceiptVoucherReport(array $filters): array
    {
        [$start, $end] = $this->query->parsePeriod($filters);
        $lines = $this->query->journalLines($start, $end, null, null, 'RV');

        return $this->table('Receipt Voucher Report', "Period: {$start->toDateString()} to {$end->toDateString()}", [
            ['key' => 'journal_date', 'label' => 'Date'],
            ['key' => 'voucher_no', 'label' => 'RV No'],
            ['key' => 'account_name', 'label' => 'Account'],
            ['key' => 'debit_amount', 'label' => 'Debit', 'align' => 'right', 'format' => 'money'],
            ['key' => 'credit_amount', 'label' => 'Credit', 'align' => 'right', 'format' => 'money'],
        ], $lines);
    }

    protected function reportRawMaterialStockReport(array $filters): array
    {
        [, , $asOn] = $this->query->parsePeriod($filters);
        $ids = $this->query->accountIdsByKeywords(['raw material', 'yarn', 'fabric stock'], ['Asset']);
        $rows = $this->query->accountBalancesAsOn($asOn, $ids);

        return $this->table('Raw Material Stock Report', 'As on ' . $asOn->toDateString(), [
            ['key' => 'account_name', 'label' => 'Account'],
            ['key' => 'balance', 'label' => 'Stock Value', 'align' => 'right', 'format' => 'money'],
        ], $rows);
    }

    protected function reportFabricStockReport(array $filters): array
    {
        [, , $asOn] = $this->query->parsePeriod($filters);
        $ids = $this->query->accountIdsByKeywords(['fabric', 'yarn'], ['Asset']);
        $rows = $this->query->accountBalancesAsOn($asOn, $ids);

        return $this->table('Fabric Stock Report', 'As on ' . $asOn->toDateString(), [
            ['key' => 'account_name', 'label' => 'Account'],
            ['key' => 'balance', 'label' => 'Balance', 'align' => 'right', 'format' => 'money'],
        ], $rows);
    }

    protected function reportAccessoriesStockReport(array $filters): array
    {
        [, , $asOn] = $this->query->parsePeriod($filters);
        $ids = $this->query->accountIdsByKeywords(['trim', 'accessories', 'button', 'zipper'], ['Asset']);
        $rows = $this->query->accountBalancesAsOn($asOn, $ids);

        return $this->table('Accessories Stock Report', 'As on ' . $asOn->toDateString(), [
            ['key' => 'account_name', 'label' => 'Account'],
            ['key' => 'balance', 'label' => 'Balance', 'align' => 'right', 'format' => 'money'],
        ], $rows);
    }

    protected function reportIssueReturnReport(array $filters): array
    {
        [$start, $end] = $this->query->parsePeriod($filters);
        $ids = $this->query->accountIdsByKeywords(['issue', 'return', 'inventory', 'stock'], ['Asset', 'Expense']);
        $lines = $this->query->journalLines($start, $end, $ids);

        return $this->table('Issue & Return Report', "Period: {$start->toDateString()} to {$end->toDateString()}", [
            ['key' => 'journal_date', 'label' => 'Date'],
            ['key' => 'voucher_no', 'label' => 'Voucher'],
            ['key' => 'account_name', 'label' => 'Account'],
            ['key' => 'debit_amount', 'label' => 'Issue (Dr)', 'align' => 'right', 'format' => 'money'],
            ['key' => 'credit_amount', 'label' => 'Return (Cr)', 'align' => 'right', 'format' => 'money'],
        ], $lines, [], [$this->proxyNote('Inventory store')]);
    }

    protected function reportStoreLedger(array $filters): array
    {
        [$start, $end] = $this->query->parsePeriod($filters);
        $ids = $this->query->accountIdsByKeywords(['stock', 'inventory', 'fabric', 'trim', 'yarn', 'wip', 'finished'], ['Asset']);
        $lines = $this->query->journalLines($start, $end, $ids);

        return $this->table('Store Ledger', "Period: {$start->toDateString()} to {$end->toDateString()}", [
            ['key' => 'journal_date', 'label' => 'Date'],
            ['key' => 'voucher_no', 'label' => 'Voucher'],
            ['key' => 'account_name', 'label' => 'Store Account'],
            ['key' => 'debit_amount', 'label' => 'In', 'align' => 'right', 'format' => 'money'],
            ['key' => 'credit_amount', 'label' => 'Out', 'align' => 'right', 'format' => 'money'],
        ], $lines);
    }

    protected function reportStockValuationReport(array $filters): array
    {
        [, , $asOn] = $this->query->parsePeriod($filters);
        $ids = $this->query->accountIdsByKeywords(['stock', 'inventory', 'fabric', 'yarn', 'trim', 'wip', 'finished'], ['Asset']);
        $rows = $this->query->accountBalancesAsOn($asOn, $ids);

        return $this->table('Stock Valuation Report', 'As on ' . $asOn->toDateString(), [
            ['key' => 'account_code', 'label' => 'Code'],
            ['key' => 'account_name', 'label' => 'Account'],
            ['key' => 'balance', 'label' => 'Valuation', 'align' => 'right', 'format' => 'money'],
        ], $rows, ['Total Stock Value' => $this->money((float) $rows->sum('balance'))]);
    }
}
