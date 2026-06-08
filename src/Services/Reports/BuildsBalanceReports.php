<?php

namespace ME\Erpaccount\Services\Reports;

trait BuildsBalanceReports
{
    protected function reportAccountBalanceStatement(array $filters): array
    {
        [, , $asOn] = $this->query->parsePeriod($filters);
        $rows = $this->query->accountBalancesAsOn($asOn)->filter(fn ($r) => abs((float) $r->balance) > 0.009);

        return $this->table(
            'Account Balance Statement',
            'As on ' . $asOn->toDateString(),
            [
                ['key' => 'account_code', 'label' => 'Code'],
                ['key' => 'account_name', 'label' => 'Account'],
                ['key' => 'account_type', 'label' => 'Type'],
                ['key' => 'balance', 'label' => 'Balance', 'align' => 'right', 'format' => 'money'],
            ],
            $rows
        );
    }

    protected function reportPartyLedgerBalance(array $filters): array
    {
        [$start, $end] = $this->query->parsePeriod($filters);
        $rows = \Illuminate\Support\Facades\DB::table('acc_journal_details as jd')
            ->join('acc_journal_masters as jm', function ($join) {
                \ME\Erpaccount\Support\JournalQueryScopes::activeMasterOnJoin($join);
            })
            ->whereBetween('jm.journal_date', [$start->toDateString(), $end->toDateString()])
            ->whereIn('jd.party_type', ['Buyer', 'Supplier', 'Employee'])
            ->whereNotNull('jd.party_id')
            ->groupBy('jd.party_type', 'jd.party_id')
            ->select(['jd.party_type', 'jd.party_id'])
            ->selectRaw('COALESCE(SUM(jd.debit_amount), 0) as total_debit')
            ->selectRaw('COALESCE(SUM(jd.credit_amount), 0) as total_credit')
            ->selectRaw('COALESCE(SUM(jd.debit_amount - jd.credit_amount), 0) as net_balance')
            ->orderBy('jd.party_type')
            ->get()
            ->map(function ($row) {
                $row->party_label = $row->party_type . ' #' . $row->party_id;

                return $row;
            });

        return $this->table(
            'Party-wise Ledger Balance',
            "Period: {$start->toDateString()} to {$end->toDateString()}",
            [
                ['key' => 'party_label', 'label' => 'Party'],
                ['key' => 'total_debit', 'label' => 'Debit', 'align' => 'right', 'format' => 'money'],
                ['key' => 'total_credit', 'label' => 'Credit', 'align' => 'right', 'format' => 'money'],
                ['key' => 'net_balance', 'label' => 'Net', 'align' => 'right', 'format' => 'money'],
            ],
            $rows
        );
    }

    protected function reportMonthlyClosingReport(array $filters): array
    {
        return $this->reportTrialBalance($filters);
    }

    protected function reportYearEndClosingReport(array $filters): array
    {
        $filters['start_date'] = $filters['start_date'] ?? now()->startOfYear()->toDateString();
        $filters['end_date'] = $filters['end_date'] ?? now()->endOfYear()->toDateString();

        $report = $this->reportTrialBalance($filters);
        $report['title'] = 'Year End Closing Report';

        return $report;
    }

    protected function reportOpeningVsClosingComparison(array $filters): array
    {
        [$start, $end] = $this->query->parsePeriod($filters);
        $openingDate = $start->copy()->subDay();
        $opening = $this->query->accountBalancesAsOn($openingDate)->keyBy('account_id');
        $closing = $this->query->accountBalancesAsOn($end)->keyBy('account_id');

        $ids = $opening->keys()->merge($closing->keys())->unique();
        $rows = $ids->map(function ($id) use ($opening, $closing) {
            $o = $opening->get($id);
            $c = $closing->get($id);

            return (object) [
                'account_code' => $c->account_code ?? $o->account_code ?? '',
                'account_name' => $c->account_name ?? $o->account_name ?? '',
                'opening_balance' => round((float) ($o->balance ?? 0), 2),
                'closing_balance' => round((float) ($c->balance ?? 0), 2),
                'variance' => round((float) ($c->balance ?? 0) - (float) ($o->balance ?? 0), 2),
            ];
        })->filter(fn ($r) => abs($r->opening_balance) > 0 || abs($r->closing_balance) > 0)->values();

        return $this->table(
            'Opening vs Closing Balance Comparison',
            "Opening as of {$openingDate->toDateString()} | Closing as of {$end->toDateString()}",
            [
                ['key' => 'account_code', 'label' => 'Code'],
                ['key' => 'account_name', 'label' => 'Account'],
                ['key' => 'opening_balance', 'label' => 'Opening', 'align' => 'right', 'format' => 'money'],
                ['key' => 'closing_balance', 'label' => 'Closing', 'align' => 'right', 'format' => 'money'],
                ['key' => 'variance', 'label' => 'Variance', 'align' => 'right', 'format' => 'money'],
            ],
            $rows
        );
    }

    protected function reportSuspenseAccountReport(array $filters): array
    {
        [, , $asOn] = $this->query->parsePeriod($filters);
        $ids = $this->query->accountIdsByKeywords(['suspense', 'temporary', 'clearing']);
        $rows = $this->query->accountBalancesAsOn($asOn, $ids)->filter(fn ($r) => abs((float) $r->balance) > 0.009);

        return $this->table(
            'Suspense / Clearing Account Report',
            'As on ' . $asOn->toDateString(),
            [
                ['key' => 'account_code', 'label' => 'Code'],
                ['key' => 'account_name', 'label' => 'Account'],
                ['key' => 'balance', 'label' => 'Balance', 'align' => 'right', 'format' => 'money'],
            ],
            $rows,
            [],
            ['Review and clear suspense balances before period close.']
        );
    }
}
