<?php

namespace ME\Erpaccount\Services\Reports;

trait BuildsManagementReports
{
    protected function agingReport(array $filters, string $partyType, string $title): array
    {
        [, , $asOn] = $this->query->parsePeriod($filters);
        $ids = $this->query->accountIdsByKeywords(
            $partyType === 'Buyer' ? ['receivable'] : ['payable'],
            $partyType === 'Buyer' ? ['Asset'] : ['Liability']
        );

        $rows = \Illuminate\Support\Facades\DB::table('acc_journal_details as jd')
            ->join('acc_journal_masters as jm', function ($join) {
                \ME\Erpaccount\Support\JournalQueryScopes::activeMasterOnJoin($join);
            })
            ->where('jd.party_type', $partyType)
            ->whereIn('jd.account_id', $ids)
            ->whereDate('jm.journal_date', '<=', $asOn->toDateString())
            ->groupBy('jd.party_id')
            ->selectRaw('jd.party_id as party_id')
            ->selectRaw('COALESCE(SUM(jd.debit_amount - jd.credit_amount), 0) as balance')
            ->selectRaw('MIN(jm.journal_date) as oldest_date')
            ->selectRaw('DATEDIFF(?, MIN(jm.journal_date)) as age_days', [$asOn->toDateString()])
            ->havingRaw('ABS(COALESCE(SUM(jd.debit_amount - jd.credit_amount), 0)) > 0.009')
            ->orderByDesc('balance')
            ->get();

        return $this->table($title, 'As on ' . $asOn->toDateString(), [
            ['key' => 'party_id', 'label' => $partyType . ' ID'],
            ['key' => 'oldest_date', 'label' => 'Oldest Txn'],
            ['key' => 'age_days', 'label' => 'Age (Days)'],
            ['key' => 'balance', 'label' => 'Outstanding', 'align' => 'right', 'format' => 'money'],
        ], $rows);
    }

    protected function reportAgingReceivable(array $filters): array
    {
        return $this->agingReport($filters, 'Buyer', 'Aging Report (Receivable)');
    }

    protected function reportAgingPayable(array $filters): array
    {
        return $this->agingReport($filters, 'Supplier', 'Aging Report (Payable)');
    }

    protected function reportExportReceivableAging(array $filters): array
    {
        $report = $this->agingReport($filters, 'Buyer', 'Export Receivable Aging');
        $report['notes'][] = 'Buyer receivable aging from export sales ledger.';

        return $report;
    }

    protected function reportPartyOutstandingReport(array $filters): array
    {
        return $this->reportPartyLedgerBalance($filters);
    }

    protected function reportAuditTrailReport(array $filters): array
    {
        [$start, $end] = $this->query->parsePeriod($filters);
        $rows = \Illuminate\Support\Facades\DB::table('acc_journal_masters as jm')
            ->whereBetween('jm.journal_date', [$start->toDateString(), $end->toDateString()])
            ->orderByDesc('jm.journal_id')
            ->select([
                'jm.journal_date',
                'jm.voucher_no',
                'jm.source_module',
                'jm.narration',
                'jm.created_by',
                'jm.is_voided',
                'jm.void_reason',
            ])
            ->get();

        return $this->table('Audit Trail Report', "Period: {$start->toDateString()} to {$end->toDateString()}", [
            ['key' => 'journal_date', 'label' => 'Date'],
            ['key' => 'voucher_no', 'label' => 'Voucher'],
            ['key' => 'source_module', 'label' => 'Source'],
            ['key' => 'created_by', 'label' => 'User ID'],
            ['key' => 'is_voided', 'label' => 'Voided'],
            ['key' => 'narration', 'label' => 'Narration'],
        ], $rows);
    }

    protected function reportExceptionMissingEntryReport(array $filters): array
    {
        [$start, $end] = $this->query->parsePeriod($filters);
        $rows = \Illuminate\Support\Facades\DB::table('acc_journal_masters as jm')
            ->join('acc_journal_details as jd', 'jm.journal_id', '=', 'jd.journal_id')
            ->whereBetween('jm.journal_date', [$start->toDateString(), $end->toDateString()])
            ->where('jm.is_voided', false)
            ->groupBy('jm.journal_id', 'jm.voucher_no', 'jm.journal_date')
            ->select(['jm.voucher_no', 'jm.journal_date'])
            ->selectRaw('COALESCE(SUM(jd.debit_amount), 0) as total_debit')
            ->selectRaw('COALESCE(SUM(jd.credit_amount), 0) as total_credit')
            ->havingRaw('ABS(COALESCE(SUM(jd.debit_amount), 0) - COALESCE(SUM(jd.credit_amount), 0)) > 0.01')
            ->get()
            ->map(function ($row) {
                $row->issue = 'Unbalanced voucher';

                return $row;
            });

        return $this->table('Exception / Missing Entry Report', "Period: {$start->toDateString()} to {$end->toDateString()}", [
            ['key' => 'journal_date', 'label' => 'Date'],
            ['key' => 'voucher_no', 'label' => 'Voucher'],
            ['key' => 'total_debit', 'label' => 'Debit', 'align' => 'right', 'format' => 'money'],
            ['key' => 'total_credit', 'label' => 'Credit', 'align' => 'right', 'format' => 'money'],
            ['key' => 'issue', 'label' => 'Issue'],
        ], $rows);
    }

    protected function reportMonthlyManagementPack(array $filters): array
    {
        $pl = $this->reportProfitAndLoss($filters);
        $bs = $this->reportBalanceSheet($filters);
        $cash = $this->reportDailyCashPositionReport($filters);

        $rows = collect([
            (object) ['metric' => 'Net Profit / (Loss)', 'value' => $pl['summary']['Net Profit / (Loss)'] ?? '0.00'],
            (object) ['metric' => 'Total Assets', 'value' => $bs['summary']['Total Assets'] ?? '0.00'],
            (object) ['metric' => 'Total Cash & Bank', 'value' => $cash['summary']['Total Cash & Bank'] ?? '0.00'],
        ]);

        return $this->table(
            'Monthly Management Report Pack',
            $pl['subtitle'],
            [
                ['key' => 'metric', 'label' => 'KPI'],
                ['key' => 'value', 'label' => 'Value'],
            ],
            $rows,
            [],
            ['Summary pack compiled from P&L, Balance Sheet, and Cash Position.']
        );
    }

    protected function reportOrderBookingVsShipmentTracking(array $filters): array
    {
        return $this->reportOrderWiseShipmentReport($filters);
    }

    protected function reportStyleProfitabilityReport(array $filters): array
    {
        return $this->reportStyleWiseCostingReport($filters);
    }

    protected function reportBuyerProfitContributionReport(array $filters): array
    {
        return $this->reportBuyerWiseSalesReport($filters);
    }

    protected function reportShipmentDelayReport(array $filters): array
    {
        $report = $this->reportOrderWiseShipmentReport($filters);
        $report['title'] = 'Shipment Delay Report';
        $report['notes'][] = $this->proxyNote('Commercial shipment schedule');

        return $report;
    }

    protected function reportExportComplianceReport(array $filters): array
    {
        $report = $this->reportExportSalesRegister($filters);
        $report['title'] = 'Export Compliance Report (Document Checklist Proxy)';
        $report['notes'][] = $this->proxyNote('Commercial export documents');

        return $report;
    }

    protected function reportDutyDrawbackBondUtilizationReport(array $filters): array
    {
        [$start, $end] = $this->query->parsePeriod($filters);
        $ids = $this->query->accountIdsByKeywords(['duty', 'drawback', 'bond', 'customs'], ['Expense', 'Liability', 'Asset']);
        $lines = $this->query->journalLines($start, $end, $ids);

        return $this->table('Duty Drawback / Bond Utilization Report', "Period: {$start->toDateString()} to {$end->toDateString()}", [
            ['key' => 'journal_date', 'label' => 'Date'],
            ['key' => 'voucher_no', 'label' => 'Voucher'],
            ['key' => 'account_name', 'label' => 'Account'],
            ['key' => 'debit_amount', 'label' => 'Debit', 'align' => 'right', 'format' => 'money'],
            ['key' => 'credit_amount', 'label' => 'Credit', 'align' => 'right', 'format' => 'money'],
        ], $lines, [], [$this->proxyNote('Customs bond module')]);
    }
}
