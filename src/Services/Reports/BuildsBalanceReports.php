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

    protected function reportAccountBalanceComparative(array $filters): array
    {
        [, , $currDate] = $this->query->parsePeriod($filters);
        $prevDate = !empty($filters['prev_date'])
            ? \Carbon\Carbon::parse($filters['prev_date'])->endOfDay()
            : $currDate->copy()->subYear();

        $curr = $this->query->accountBalancesWithGroupAsOn($currDate)->keyBy('account_id');
        $prev = $this->query->accountBalancesWithGroupAsOn($prevDate)->keyBy('account_id');

        $allIds = $curr->keys()->merge($prev->keys())->unique();

        $accounts = $allIds->map(function ($id) use ($curr, $prev) {
            $c = $curr->get($id);
            $p = $prev->get($id);
            $base = $c ?? $p;
            return (object) [
                'account_id'   => $base->account_id,
                'account_code' => $base->account_code,
                'account_name' => $base->account_name,
                'account_type' => $base->account_type,
                'group_name'   => $base->group_name,
                'group_code'   => $base->group_code,
                'bal_curr'     => round((float) ($c->balance ?? 0), 2),
                'bal_prev'     => round((float) ($p->balance ?? 0), 2),
            ];
        })->filter(fn ($r) => abs($r->bal_curr) > 0.009 || abs($r->bal_prev) > 0.009);

        $typeOrder = ['Current Assets', 'Non Current Assets', 'Asset', 'Current Liabilities', 'Non Current Liabilities', 'Liability', 'Equity', 'Revenue', 'Income', 'Expense'];
        $grouped = $accounts->groupBy('account_type')
            ->sortBy(fn ($_, $type) => array_search($type, $typeOrder) !== false ? array_search($type, $typeOrder) : 99);

        $rows = collect();
        foreach ($grouped as $type => $typeAccounts) {
            $rows->push((object) ['_row_type' => 'type_header', 'label' => $type, 'bal_curr' => null, 'bal_prev' => null]);
            $byGroup = $typeAccounts->groupBy('group_name')->sortBy('group_code');
            $typeSumCurr = 0.0;
            $typeSumPrev = 0.0;

            foreach ($byGroup as $groupName => $groupAccounts) {
                $rows->push((object) ['_row_type' => 'group_header', 'label' => $groupName, 'bal_curr' => null, 'bal_prev' => null]);
                $groupSumCurr = 0.0;
                $groupSumPrev = 0.0;

                foreach ($groupAccounts->sortBy('account_code') as $acc) {
                    $rows->push($acc);
                    $groupSumCurr += $acc->bal_curr;
                    $groupSumPrev += $acc->bal_prev;
                }

                $rows->push((object) ['_row_type' => 'group_total', 'label' => 'Total', 'bal_curr' => round($groupSumCurr, 2), 'bal_prev' => round($groupSumPrev, 2)]);
                $typeSumCurr += $groupSumCurr;
                $typeSumPrev += $groupSumPrev;
            }

            $rows->push((object) ['_row_type' => 'type_total', 'label' => 'Account Type Total', 'bal_curr' => round($typeSumCurr, 2), 'bal_prev' => round($typeSumPrev, 2)]);
        }

        return [
            'title'       => 'Account Balance — Comparative Statement',
            'subtitle'    => 'Current: ' . $currDate->toDateString() . '   |   Previous: ' . $prevDate->toDateString(),
            'render_type' => 'comparative',
            'curr_label'  => $currDate->format('Y') . ' Balance',
            'prev_label'  => $prevDate->format('Y') . ' Balance',
            'columns'     => [
                ['key' => 'account_type', 'label' => 'Account Type'],
                ['key' => 'group_name',   'label' => 'Account Group'],
                ['key' => 'account_name', 'label' => 'Account Title'],
                ['key' => 'bal_curr',     'label' => $currDate->format('Y') . ' Balance', 'align' => 'right', 'format' => 'money'],
                ['key' => 'bal_prev',     'label' => $prevDate->format('Y') . ' Balance', 'align' => 'right', 'format' => 'money'],
            ],
            'rows'    => $rows->values(),
            'summary' => [],
            'notes'   => ['Balances are cumulative as on the selected date. Zero-balance accounts are hidden.'],
        ];
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
