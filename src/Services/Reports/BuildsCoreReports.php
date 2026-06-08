<?php

namespace ME\Erpaccount\Services\Reports;

use ME\Erpaccount\Support\JournalQueryScopes;

trait BuildsCoreReports
{
    protected function reportTrialBalance(array $filters): array
    {
        [$start, $end] = $this->query->parsePeriod($filters);
        $rows = $this->query->periodMovement($start, $end)->map(function ($row) {
            $closing = in_array($row->account_type, ['Asset', 'Expense'], true)
                ? (float) $row->period_debit - (float) $row->period_credit
                : (float) $row->period_credit - (float) $row->period_debit;

            $row->closing_balance = round($closing, 2);

            return $row;
        })->filter(fn ($r) => abs((float) $r->period_debit) > 0 || abs((float) $r->period_credit) > 0 || abs((float) $r->closing_balance) > 0);

        return $this->table(
            'Trial Balance',
            "Period: {$start->toDateString()} to {$end->toDateString()}",
            [
                ['key' => 'account_code', 'label' => 'Code'],
                ['key' => 'account_name', 'label' => 'Account'],
                ['key' => 'account_type', 'label' => 'Type'],
                ['key' => 'period_debit', 'label' => 'Debit', 'align' => 'right', 'format' => 'money'],
                ['key' => 'period_credit', 'label' => 'Credit', 'align' => 'right', 'format' => 'money'],
                ['key' => 'closing_balance', 'label' => 'Closing', 'align' => 'right', 'format' => 'money'],
            ],
            $rows,
            [
                'Total Debit' => $this->money((float) $rows->sum('period_debit')),
                'Total Credit' => $this->money((float) $rows->sum('period_credit')),
            ]
        );
    }

    protected function reportProfitAndLoss(array $filters): array
    {
        [$start, $end] = $this->query->parsePeriod($filters);
        $rows = \Illuminate\Support\Facades\DB::table('acc_chart_of_accounts as coa')
            ->join('acc_journal_details as jd', 'jd.account_id', '=', 'coa.account_id')
            ->join('acc_journal_masters as jm', function ($join) {
                JournalQueryScopes::activeMasterOnJoin($join);
            })
            ->whereBetween('jm.journal_date', [$start->toDateString(), $end->toDateString()])
            ->whereIn('coa.account_type', ['Revenue', 'Expense'])
            ->groupBy('coa.account_id', 'coa.account_name', 'coa.account_type')
            ->orderBy('coa.account_type')
            ->orderBy('coa.account_name')
            ->select(['coa.account_name', 'coa.account_type'])
            ->selectRaw("COALESCE(SUM(CASE WHEN coa.account_type = 'Revenue' THEN jd.credit_amount - jd.debit_amount ELSE jd.debit_amount - jd.credit_amount END), 0) as amount")
            ->get();

        $revenue = (float) $rows->where('account_type', 'Revenue')->sum('amount');
        $expense = (float) $rows->where('account_type', 'Expense')->sum('amount');

        return $this->table(
            'Profit & Loss Account',
            "Period: {$start->toDateString()} to {$end->toDateString()}",
            [
                ['key' => 'account_name', 'label' => 'Account'],
                ['key' => 'account_type', 'label' => 'Type'],
                ['key' => 'amount', 'label' => 'Amount', 'align' => 'right', 'format' => 'money'],
            ],
            $rows,
            [
                'Total Revenue' => $this->money($revenue),
                'Total Expenses' => $this->money($expense),
                'Net Profit / (Loss)' => $this->money($revenue - $expense),
            ]
        );
    }

    protected function reportBalanceSheet(array $filters): array
    {
        [, , $asOn] = $this->query->parsePeriod($filters);
        $rows = $this->query->accountBalancesAsOn($asOn, null, ['Asset', 'Liability', 'Equity'])
            ->filter(fn ($r) => abs((float) $r->balance) > 0.009);

        return $this->table(
            'Balance Sheet',
            'As on ' . $asOn->toDateString(),
            [
                ['key' => 'account_code', 'label' => 'Code'],
                ['key' => 'account_name', 'label' => 'Account'],
                ['key' => 'account_type', 'label' => 'Type'],
                ['key' => 'balance', 'label' => 'Amount', 'align' => 'right', 'format' => 'money'],
            ],
            $rows,
            [
                'Total Assets' => $this->money((float) $rows->where('account_type', 'Asset')->sum('balance')),
                'Total Liabilities + Equity' => $this->money(
                    (float) $rows->whereIn('account_type', ['Liability', 'Equity'])->sum('balance')
                ),
            ]
        );
    }

    protected function reportCashFlowStatement(array $filters): array
    {
        [$start, $end] = $this->query->parsePeriod($filters);
        $cashIds = $this->query->accountIdsByKeywords(['cash', 'bank'], ['Asset']);
        $lines = $this->query->journalLines($start, $end, $cashIds);

        $operating = (float) $lines->sum(fn ($l) => (float) $l->debit_amount - (float) $l->credit_amount);

        $rows = collect([
            (object) ['section' => 'Operating Activities', 'amount' => $operating],
            (object) ['section' => 'Investing Activities', 'amount' => 0],
            (object) ['section' => 'Financing Activities', 'amount' => 0],
            (object) ['section' => 'Net Cash Movement', 'amount' => $operating],
        ]);

        return $this->table(
            'Cash Flow Statement (Summary)',
            "Period: {$start->toDateString()} to {$end->toDateString()}",
            [
                ['key' => 'section', 'label' => 'Section'],
                ['key' => 'amount', 'label' => 'Amount', 'align' => 'right', 'format' => 'money'],
            ],
            $rows,
            ['Net Cash Flow' => $this->money($operating)],
            ['Indirect method from cash/bank ledger movement.']
        );
    }

    protected function reportGeneralLedger(array $filters): array
    {
        [$start, $end] = $this->query->parsePeriod($filters);
        $accountId = !empty($filters['account_id']) ? (int) $filters['account_id'] : null;
        $accountIds = $accountId ? [$accountId] : null;
        $lines = $this->query->journalLines($start, $end, $accountIds);

        $running = 0.0;
        $rows = $lines->map(function ($line) use (&$running) {
            $running += (float) $line->debit_amount - (float) $line->credit_amount;
            $line->running_balance = round($running, 2);

            return $line;
        });

        return $this->table(
            'General Ledger',
            "Period: {$start->toDateString()} to {$end->toDateString()}",
            [
                ['key' => 'journal_date', 'label' => 'Date'],
                ['key' => 'voucher_no', 'label' => 'Voucher'],
                ['key' => 'account_code', 'label' => 'Code'],
                ['key' => 'account_name', 'label' => 'Account'],
                ['key' => 'narration', 'label' => 'Narration'],
                ['key' => 'debit_amount', 'label' => 'Debit', 'align' => 'right', 'format' => 'money'],
                ['key' => 'credit_amount', 'label' => 'Credit', 'align' => 'right', 'format' => 'money'],
                ['key' => 'running_balance', 'label' => 'Balance', 'align' => 'right', 'format' => 'money'],
            ],
            $rows
        );
    }

    protected function reportChartOfAccountsReport(array $filters): array
    {
        [, , $asOn] = $this->query->parsePeriod($filters);
        $rows = $this->query->accountBalancesAsOn($asOn);

        return $this->table(
            'Chart of Accounts Report',
            'Balances as on ' . $asOn->toDateString(),
            [
                ['key' => 'account_code', 'label' => 'Code'],
                ['key' => 'account_name', 'label' => 'Account'],
                ['key' => 'account_type', 'label' => 'Type'],
                ['key' => 'balance', 'label' => 'Balance', 'align' => 'right', 'format' => 'money'],
                ['key' => 'side', 'label' => 'Dr/Cr'],
            ],
            $rows
        );
    }
}
