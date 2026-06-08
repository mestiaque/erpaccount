<?php

namespace ME\Erpaccount\Services\Reports;

trait BuildsProductionReports
{
    protected function costCenterExpenseRows(array $filters, ?array $keywords = null): array
    {
        [$start, $end] = $this->query->parsePeriod($filters);
        $query = \Illuminate\Support\Facades\DB::table('acc_cost_centers as cc')
            ->join('acc_journal_details as jd', 'cc.cost_center_id', '=', 'jd.cost_center_id')
            ->join('acc_journal_masters as jm', function ($join) {
                \ME\Erpaccount\Support\JournalQueryScopes::activeMasterOnJoin($join);
            })
            ->join('acc_chart_of_accounts as coa', 'coa.account_id', '=', 'jd.account_id')
            ->whereBetween('jm.journal_date', [$start->toDateString(), $end->toDateString()])
            ->where('coa.account_type', 'Expense');

        if (!empty($keywords)) {
            $query->where(function ($q) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $q->orWhereRaw('LOWER(coa.account_name) LIKE ?', ['%' . strtolower($keyword) . '%']);
                }
            });
        }

        return [$start, $end, $query
            ->groupBy('cc.cost_center_id', 'cc.cost_center_name', 'cc.reference_id', 'coa.account_name')
            ->select(['cc.cost_center_name', 'cc.reference_id', 'coa.account_name'])
            ->selectRaw('COALESCE(SUM(jd.debit_amount - jd.credit_amount), 0) as cost_amount')
            ->orderByDesc('cost_amount')
            ->get()];
    }

    protected function reportProductionCostSheet(array $filters): array
    {
        [$start, $end, $rows] = $this->costCenterExpenseRows($filters);

        return $this->table('Production Cost Sheet', "Period: {$start->toDateString()} to {$end->toDateString()}", [
            ['key' => 'reference_id', 'label' => 'Style/Order'],
            ['key' => 'cost_center_name', 'label' => 'Cost Center'],
            ['key' => 'account_name', 'label' => 'Cost Head'],
            ['key' => 'cost_amount', 'label' => 'Amount', 'align' => 'right', 'format' => 'money'],
        ], $rows);
    }

    protected function reportStyleWiseCostingReport(array $filters): array
    {
        [$start, $end] = $this->query->parsePeriod($filters);
        $rows = \Illuminate\Support\Facades\DB::table('acc_cost_centers as cc')
            ->join('acc_journal_details as jd', 'cc.cost_center_id', '=', 'jd.cost_center_id')
            ->join('acc_journal_masters as jm', function ($join) {
                \ME\Erpaccount\Support\JournalQueryScopes::activeMasterOnJoin($join);
            })
            ->join('acc_chart_of_accounts as coa', 'coa.account_id', '=', 'jd.account_id')
            ->whereBetween('jm.journal_date', [$start->toDateString(), $end->toDateString()])
            ->groupBy('cc.cost_center_id', 'cc.cost_center_name', 'cc.reference_id')
            ->select(['cc.cost_center_name', 'cc.reference_id'])
            ->selectRaw("COALESCE(SUM(CASE WHEN coa.account_type = 'Revenue' THEN jd.credit_amount - jd.debit_amount ELSE 0 END), 0) as revenue")
            ->selectRaw("COALESCE(SUM(CASE WHEN coa.account_type = 'Expense' THEN jd.debit_amount - jd.credit_amount ELSE 0 END), 0) as cost")
            ->get()
            ->map(function ($row) {
                $row->profit = round((float) $row->revenue - (float) $row->cost, 2);

                return $row;
            });

        return $this->table('Style-wise Costing Report', "Period: {$start->toDateString()} to {$end->toDateString()}", [
            ['key' => 'reference_id', 'label' => 'Style/Order'],
            ['key' => 'cost_center_name', 'label' => 'Cost Center'],
            ['key' => 'revenue', 'label' => 'Revenue', 'align' => 'right', 'format' => 'money'],
            ['key' => 'cost', 'label' => 'Cost', 'align' => 'right', 'format' => 'money'],
            ['key' => 'profit', 'label' => 'Profit', 'align' => 'right', 'format' => 'money'],
        ], $rows);
    }

    protected function reportCmCostReport(array $filters): array
    {
        [$start, $end, $rows] = $this->costCenterExpenseRows($filters, ['cm', 'cutting', 'making', 'sewing', 'labor', 'wage']);

        return $this->table('CM (Cutting Making) Cost Report', "Period: {$start->toDateString()} to {$end->toDateString()}", [
            ['key' => 'reference_id', 'label' => 'Order'],
            ['key' => 'account_name', 'label' => 'Account'],
            ['key' => 'cost_amount', 'label' => 'CM Cost', 'align' => 'right', 'format' => 'money'],
        ], $rows);
    }

    protected function reportFobCostBreakdown(array $filters): array
    {
        [$start, $end] = $this->query->parsePeriod($filters);
        $fabric = $this->query->accountIdsByKeywords(['fabric', 'yarn'], ['Expense']);
        $trim = $this->query->accountIdsByKeywords(['trim', 'accessories'], ['Expense']);
        $cm = $this->query->accountIdsByKeywords(['cm', 'labor', 'wage'], ['Expense']);

        $sum = fn (array $ids) => (float) $this->query->journalLines($start, $end, $ids)->sum(fn ($l) => (float) $l->debit_amount - (float) $l->credit_amount);

        $rows = collect([
            (object) ['component' => 'Fabric Cost', 'amount' => round($sum($fabric), 2)],
            (object) ['component' => 'Accessories Cost', 'amount' => round($sum($trim), 2)],
            (object) ['component' => 'CM / Labor Cost', 'amount' => round($sum($cm), 2)],
        ]);

        return $this->table('FOB Cost Breakdown', "Period: {$start->toDateString()} to {$end->toDateString()}", [
            ['key' => 'component', 'label' => 'Component'],
            ['key' => 'amount', 'label' => 'Amount', 'align' => 'right', 'format' => 'money'],
        ], $rows);
    }

    protected function reportWipReport(array $filters): array
    {
        [, , $asOn] = $this->query->parsePeriod($filters);
        $ids = $this->query->accountIdsByKeywords(['wip', 'work in process', 'work in progress'], ['Asset']);
        $rows = $this->query->accountBalancesAsOn($asOn, $ids);

        return $this->table('WIP (Work in Progress) Report', 'As on ' . $asOn->toDateString(), [
            ['key' => 'account_name', 'label' => 'Account'],
            ['key' => 'balance', 'label' => 'WIP Balance', 'align' => 'right', 'format' => 'money'],
        ], $rows);
    }

    protected function reportFinishedGoodsReport(array $filters): array
    {
        [, , $asOn] = $this->query->parsePeriod($filters);
        $ids = $this->query->accountIdsByKeywords(['finished goods', 'finished', 'fg stock'], ['Asset']);
        $rows = $this->query->accountBalancesAsOn($asOn, $ids);

        return $this->table('Finished Goods Report', 'As on ' . $asOn->toDateString(), [
            ['key' => 'account_name', 'label' => 'Account'],
            ['key' => 'balance', 'label' => 'FG Balance', 'align' => 'right', 'format' => 'money'],
        ], $rows);
    }

    protected function reportOrderCostVsProfitReport(array $filters): array
    {
        return $this->reportStyleWiseCostingReport($filters);
    }
}
