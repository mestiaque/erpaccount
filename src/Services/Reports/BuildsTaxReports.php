<?php

namespace ME\Erpaccount\Services\Reports;

trait BuildsTaxReports
{
    protected function ledgerKeywordReport(
        string $title,
        array $filters,
        array $keywords,
        array $types = [],
        ?string $partyType = null
    ): array {
        [$start, $end] = $this->query->parsePeriod($filters);
        $ids = $this->query->accountIdsByKeywords($keywords, $types);
        $lines = $this->query->journalLines($start, $end, $ids, $partyType);

        return $this->table(
            $title,
            "Period: {$start->toDateString()} to {$end->toDateString()}",
            [
                ['key' => 'journal_date', 'label' => 'Date'],
                ['key' => 'voucher_no', 'label' => 'Voucher'],
                ['key' => 'account_name', 'label' => 'Account'],
                ['key' => 'narration', 'label' => 'Narration'],
                ['key' => 'debit_amount', 'label' => 'Debit', 'align' => 'right', 'format' => 'money'],
                ['key' => 'credit_amount', 'label' => 'Credit', 'align' => 'right', 'format' => 'money'],
            ],
            $lines,
            [
                'Total Debit' => $this->money((float) $lines->sum('debit_amount')),
                'Total Credit' => $this->money((float) $lines->sum('credit_amount')),
            ]
        );
    }

    protected function reportVatSalesRegister(array $filters): array
    {
        return $this->ledgerKeywordReport('VAT Sales Register', $filters, ['vat', 'tax'], ['Revenue']);
    }

    protected function reportVatPurchaseRegister(array $filters): array
    {
        return $this->ledgerKeywordReport('VAT Purchase Register', $filters, ['vat', 'tax'], ['Expense', 'Asset']);
    }

    protected function reportVatPayableReport(array $filters): array
    {
        return $this->ledgerKeywordReport('VAT Payable Report', $filters, ['vat payable', 'vat liability', 'vat'], ['Liability']);
    }

    protected function reportVatInputCreditReport(array $filters): array
    {
        return $this->ledgerKeywordReport('VAT Input Credit Report', $filters, ['vat input', 'input vat', 'vat'], ['Asset']);
    }

    protected function reportVatOutputReport(array $filters): array
    {
        return $this->ledgerKeywordReport('VAT Output Report', $filters, ['vat output', 'output vat', 'vat'], ['Liability', 'Revenue']);
    }

    protected function reportMonthlyVatReturnSummary(array $filters): array
    {
        [$start, $end] = $this->query->parsePeriod($filters);
        $output = $this->query->journalLines($start, $end, $this->query->accountIdsByKeywords(['vat'], ['Revenue', 'Liability']));
        $input = $this->query->journalLines($start, $end, $this->query->accountIdsByKeywords(['vat'], ['Expense', 'Asset']));

        $rows = collect([
            (object) ['line' => 'Output VAT (Credit side)', 'amount' => (float) $output->sum('credit_amount')],
            (object) ['line' => 'Input VAT (Debit side)', 'amount' => (float) $input->sum('debit_amount')],
            (object) ['line' => 'Net VAT Payable (Output - Input)', 'amount' => (float) $output->sum('credit_amount') - (float) $input->sum('debit_amount')],
        ]);

        return $this->table(
            'Monthly VAT Return Summary',
            "Period: {$start->toDateString()} to {$end->toDateString()}",
            [
                ['key' => 'line', 'label' => 'Description'],
                ['key' => 'amount', 'label' => 'Amount', 'align' => 'right', 'format' => 'money'],
            ],
            $rows
        );
    }

    protected function reportTdsDeductionReport(array $filters): array
    {
        return $this->ledgerKeywordReport('TDS Deduction Report', $filters, ['tds', 'source tax', 'withholding'], ['Expense', 'Liability']);
    }

    protected function reportTdsPayablePaidStatement(array $filters): array
    {
        return $this->ledgerKeywordReport('TDS Payable & Paid Statement', $filters, ['tds', 'source tax payable', 'withholding'], ['Liability']);
    }
}
