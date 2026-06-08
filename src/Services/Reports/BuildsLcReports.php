<?php

namespace ME\Erpaccount\Services\Reports;

use Illuminate\Support\Facades\Schema;

trait BuildsLcReports
{
    protected function lcRows(): \Illuminate\Support\Collection
    {
        if (!Schema::hasTable('acc_lc_financials')) {
            return collect();
        }

        return \Illuminate\Support\Facades\DB::table('acc_lc_financials')->orderByDesc('lc_finance_id')->get();
    }

    protected function reportLcOpeningReport(array $filters): array
    {
        $rows = $this->lcRows();

        return $this->table(
            'LC Opening Report',
            'All LC records in accounts module',
            [
                ['key' => 'lc_finance_id', 'label' => 'ID'],
                ['key' => 'lc_type', 'label' => 'Type'],
                ['key' => 'lc_id_reference', 'label' => 'LC Ref'],
                ['key' => 'total_lc_value', 'label' => 'LC Value', 'align' => 'right', 'format' => 'money'],
                ['key' => 'currency', 'label' => 'Currency'],
                ['key' => 'posting_status', 'label' => 'Status'],
            ],
            $rows,
            ['Total LC Value' => $this->money((float) $rows->sum('total_lc_value'))]
        );
    }

    protected function reportLcUtilizationReport(array $filters): array
    {
        $rows = $this->lcRows()->map(function ($row) {
            $limit = (float) ($row->bank_margin_limit ?? 0);
            $used = (float) ($row->bank_margin_used ?? 0);
            $row->utilization_percent = $limit > 0 ? round(($used / $limit) * 100, 2) : 0;

            return $row;
        });

        return $this->table('LC Utilization Report', 'Margin utilization from LC financial tracker', [
            ['key' => 'lc_type', 'label' => 'Type'],
            ['key' => 'lc_id_reference', 'label' => 'LC Ref'],
            ['key' => 'bank_margin_limit', 'label' => 'Limit', 'align' => 'right', 'format' => 'money'],
            ['key' => 'bank_margin_used', 'label' => 'Used', 'align' => 'right', 'format' => 'money'],
            ['key' => 'utilization_percent', 'label' => 'Utilization %', 'align' => 'right'],
        ], $rows);
    }

    protected function reportBackToBackLcReport(array $filters): array
    {
        $rows = $this->lcRows()->filter(fn ($r) => $r->lc_type === 'Back_To_Back_LC');

        return $this->table('Back-to-Back LC Report', 'BTB LC financial positions', [
            ['key' => 'lc_id_reference', 'label' => 'LC Ref'],
            ['key' => 'total_lc_value', 'label' => 'Value', 'align' => 'right', 'format' => 'money'],
            ['key' => 'outstanding_liability', 'label' => 'Outstanding', 'align' => 'right', 'format' => 'money'],
            ['key' => 'posting_status', 'label' => 'Status'],
        ], $rows);
    }

    protected function reportLcMarginReport(array $filters): array
    {
        return $this->reportLcUtilizationReport($filters);
    }

    protected function reportBankPaymentReceiptReport(array $filters): array
    {
        [$start, $end] = $this->query->parsePeriod($filters);
        $receipts = $this->query->journalLines($start, $end, null, null, 'RV');
        $payments = $this->query->journalLines($start, $end, null, null, 'PV');
        $lines = $receipts->merge($payments)->sortBy('journal_date')->values();

        return $this->table('Bank Payment & Receipt Report', "Period: {$start->toDateString()} to {$end->toDateString()}", [
            ['key' => 'journal_date', 'label' => 'Date'],
            ['key' => 'voucher_no', 'label' => 'Voucher'],
            ['key' => 'account_name', 'label' => 'Account'],
            ['key' => 'debit_amount', 'label' => 'Debit', 'align' => 'right', 'format' => 'money'],
            ['key' => 'credit_amount', 'label' => 'Credit', 'align' => 'right', 'format' => 'money'],
        ], $lines);
    }

    protected function reportExportProceedsRealizationReport(array $filters): array
    {
        return $this->reportExportRealizationReport($filters);
    }

    protected function reportBtbPaymentStatus(array $filters): array
    {
        $rows = $this->lcRows()->filter(fn ($r) => $r->lc_type === 'Back_To_Back_LC')->map(function ($row) {
            $row->payment_status = (float) ($row->outstanding_liability ?? 0) > 0 ? 'Outstanding' : 'Settled';

            return $row;
        });

        return $this->table('BTB Payment Status', 'Back-to-back LC liability status', [
            ['key' => 'lc_id_reference', 'label' => 'LC Ref'],
            ['key' => 'outstanding_liability', 'label' => 'Outstanding', 'align' => 'right', 'format' => 'money'],
            ['key' => 'payment_status', 'label' => 'Status'],
        ], $rows);
    }
}
