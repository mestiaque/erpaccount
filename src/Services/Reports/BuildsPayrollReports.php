<?php

namespace ME\Erpaccount\Services\Reports;

trait BuildsPayrollReports
{
    protected function reportSalarySheet(array $filters): array
    {
        [$start, $end] = $this->query->parsePeriod($filters);
        $ids = $this->query->accountIdsByKeywords(['salary', 'wage', 'payroll'], ['Expense', 'Liability']);
        $lines = $this->query->journalLines($start, $end, $ids);

        return $this->table('Salary Sheet', "Period: {$start->toDateString()} to {$end->toDateString()}", [
            ['key' => 'journal_date', 'label' => 'Date'],
            ['key' => 'voucher_no', 'label' => 'Voucher'],
            ['key' => 'account_name', 'label' => 'Account'],
            ['key' => 'debit_amount', 'label' => 'Debit', 'align' => 'right', 'format' => 'money'],
            ['key' => 'credit_amount', 'label' => 'Credit', 'align' => 'right', 'format' => 'money'],
        ], $lines);
    }

    protected function reportOvertimeReport(array $filters): array
    {
        [$start, $end] = $this->query->parsePeriod($filters);
        $ids = $this->query->accountIdsByKeywords(['overtime', 'ot', 'wage'], ['Expense']);
        $lines = $this->query->journalLines($start, $end, $ids);

        return $this->table('Overtime (OT) Report', "Period: {$start->toDateString()} to {$end->toDateString()}", [
            ['key' => 'journal_date', 'label' => 'Date'],
            ['key' => 'voucher_no', 'label' => 'Voucher'],
            ['key' => 'account_name', 'label' => 'Account'],
            ['key' => 'debit_amount', 'label' => 'Amount', 'align' => 'right', 'format' => 'money'],
        ], $lines, [], [$this->proxyNote('HR Attendance')]);
    }

    protected function reportAttendanceSalaryIntegrationReport(array $filters): array
    {
        $report = $this->reportSalarySheet($filters);
        $report['title'] = 'Attendance Salary Integration Report';
        $report['notes'][] = $this->proxyNote('HR');

        return $report;
    }

    protected function reportWorkerWiseCostReport(array $filters): array
    {
        [$start, $end] = $this->query->parsePeriod($filters);
        $ids = $this->query->accountIdsByKeywords(['wage', 'piece', 'worker', 'salary'], ['Expense']);
        $rows = \Illuminate\Support\Facades\DB::table('acc_journal_details as jd')
            ->join('acc_journal_masters as jm', function ($join) {
                \ME\Erpaccount\Support\JournalQueryScopes::activeMasterOnJoin($join);
            })
            ->join('acc_chart_of_accounts as coa', 'coa.account_id', '=', 'jd.account_id')
            ->whereBetween('jm.journal_date', [$start->toDateString(), $end->toDateString()])
            ->where('jd.party_type', 'Employee')
            ->whereIn('jd.account_id', $ids)
            ->groupBy('jd.party_id')
            ->selectRaw('jd.party_id as employee_id')
            ->selectRaw('COALESCE(SUM(jd.debit_amount - jd.credit_amount), 0) as labor_cost')
            ->orderByDesc('labor_cost')
            ->get();

        return $this->table('Worker-wise Cost Report', "Period: {$start->toDateString()} to {$end->toDateString()}", [
            ['key' => 'employee_id', 'label' => 'Employee ID'],
            ['key' => 'labor_cost', 'label' => 'Cost', 'align' => 'right', 'format' => 'money'],
        ], $rows);
    }

    protected function reportBonusReport(array $filters): array
    {
        [$start, $end] = $this->query->parsePeriod($filters);
        $ids = $this->query->accountIdsByKeywords(['bonus', 'eid', 'festival'], ['Expense']);
        $lines = $this->query->journalLines($start, $end, $ids);

        return $this->table('Bonus Report', "Period: {$start->toDateString()} to {$end->toDateString()}", [
            ['key' => 'journal_date', 'label' => 'Date'],
            ['key' => 'voucher_no', 'label' => 'Voucher'],
            ['key' => 'account_name', 'label' => 'Account'],
            ['key' => 'debit_amount', 'label' => 'Amount', 'align' => 'right', 'format' => 'money'],
        ], $lines);
    }

    protected function reportPfGratuityReport(array $filters): array
    {
        [$start, $end] = $this->query->parsePeriod($filters);
        $ids = $this->query->accountIdsByKeywords(['provident', 'pf', 'gratuity'], ['Liability', 'Expense']);
        $lines = $this->query->journalLines($start, $end, $ids);

        return $this->table('PF / Gratuity Report', "Period: {$start->toDateString()} to {$end->toDateString()}", [
            ['key' => 'journal_date', 'label' => 'Date'],
            ['key' => 'voucher_no', 'label' => 'Voucher'],
            ['key' => 'account_name', 'label' => 'Account'],
            ['key' => 'debit_amount', 'label' => 'Debit', 'align' => 'right', 'format' => 'money'],
            ['key' => 'credit_amount', 'label' => 'Credit', 'align' => 'right', 'format' => 'money'],
        ], $lines);
    }
}
