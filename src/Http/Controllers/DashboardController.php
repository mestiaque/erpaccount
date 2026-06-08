<?php

namespace ME\Erpaccount\Http\Controllers;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ME\Erpaccount\Models\FinancialPeriod;
use ME\Erpaccount\Support\JournalQueryScopes;

class DashboardController extends Controller
{
    public function index()
    {
        [$fyStart, $fyEnd, $periodLabel] = $this->resolveFinancialYearWindow();

        $balanceByType = DB::table('acc_journal_details as jd')
            ->join('acc_journal_masters as jm', function ($join) {
                JournalQueryScopes::activeMasterOnJoin($join);
            })
            ->join('acc_chart_of_accounts as coa', 'coa.account_id', '=', 'jd.account_id')
            ->whereIn('coa.account_type', ['Asset', 'Liability', 'Equity'])
            ->groupBy('coa.account_type')
            ->select('coa.account_type')
            ->selectRaw("COALESCE(SUM(CASE WHEN coa.account_type = 'Asset' THEN jd.debit_amount - jd.credit_amount WHEN coa.account_type IN ('Liability', 'Equity') THEN jd.credit_amount - jd.debit_amount ELSE 0 END), 0) as net_balance")
            ->pluck('net_balance', 'account_type');

        $totalAssets = round((float) ($balanceByType['Asset'] ?? 0), 2);
        $totalLiabilities = round((float) ($balanceByType['Liability'] ?? 0), 2);
        $totalEquity = round((float) ($balanceByType['Equity'] ?? 0), 2);

        $ytdIncomeExpenseQuery = DB::table('acc_journal_masters as jm')
            ->join('acc_journal_details as jd', 'jm.journal_id', '=', 'jd.journal_id')
            ->join('acc_chart_of_accounts as coa', 'coa.account_id', '=', 'jd.account_id')
            ->whereBetween('jm.journal_date', [$fyStart->toDateString(), $fyEnd->toDateString()])
            ->whereIn('coa.account_type', ['Revenue', 'Expense']);

        JournalQueryScopes::restrictActiveMasters($ytdIncomeExpenseQuery, 'jm');

        $ytdIncomeExpense = $ytdIncomeExpenseQuery
            ->selectRaw("COALESCE(SUM(CASE WHEN coa.account_type = 'Revenue' THEN jd.credit_amount - jd.debit_amount ELSE 0 END), 0) as total_revenue")
            ->selectRaw("COALESCE(SUM(CASE WHEN coa.account_type = 'Expense' THEN jd.debit_amount - jd.credit_amount ELSE 0 END), 0) as total_expenses")
            ->first();

        $totalRevenueYtd = round((float) ($ytdIncomeExpense->total_revenue ?? 0), 2);
        $totalExpenseYtd = round((float) ($ytdIncomeExpense->total_expenses ?? 0), 2);
        $netProfitLossYtd = round($totalRevenueYtd - $totalExpenseYtd, 2);

        $cashBankAccountIds = [];
        if (Schema::hasTable('acc_bank_accounts')) {
            $cashBankAccountIds = DB::table('acc_bank_accounts')
                ->where('is_active', true)
                ->pluck('account_id')
                ->all();
        }

        $cashAndBankBalance = round((float) DB::table('acc_journal_details as jd')
            ->join('acc_journal_masters as jm', function ($join) {
                JournalQueryScopes::activeMasterOnJoin($join);
            })
            ->join('acc_chart_of_accounts as coa', 'coa.account_id', '=', 'jd.account_id')
            ->where('coa.account_type', 'Asset')
            ->where(function ($query) use ($cashBankAccountIds) {
                $query->whereRaw('LOWER(coa.account_name) LIKE ?', ['%cash%'])
                    ->orWhereRaw('LOWER(coa.account_name) LIKE ?', ['%bank%']);

                if (!empty($cashBankAccountIds)) {
                    $query->orWhereIn('coa.account_id', $cashBankAccountIds);
                }
            })
            ->selectRaw('COALESCE(SUM(jd.debit_amount) - SUM(jd.credit_amount), 0) as amount')
            ->value('amount'), 2);

        $lcLiabilityExposure = 0.0;
        if (Schema::hasTable('acc_lc_financials')) {
            if (Schema::hasColumn('acc_lc_financials', 'outstanding_liability')) {
                $lcLiabilityExposure = round((float) DB::table('acc_lc_financials')
                    ->selectRaw('COALESCE(SUM(outstanding_liability), 0) as amount')
                    ->value('amount'), 2);
            } elseif (Schema::hasColumn('acc_lc_financials', 'bank_margin_used')) {
                // Fallback for older schemas where outstanding liability column is not yet migrated.
                $lcLiabilityExposure = round((float) DB::table('acc_lc_financials')
                    ->selectRaw('COALESCE(SUM(bank_margin_used), 0) as amount')
                    ->value('amount'), 2);
            } elseif (Schema::hasColumn('acc_lc_financials', 'total_lc_value')) {
                $lcLiabilityExposure = round((float) DB::table('acc_lc_financials')
                    ->selectRaw('COALESCE(SUM(total_lc_value), 0) as amount')
                    ->value('amount'), 2);
            }
        }

        $pendingInventory = 0;
        if (Schema::hasTable('acc_inventory_posting_logs')) {
            $pendingInventory = (int) DB::table('acc_inventory_posting_logs')
                ->where('posting_status', 'Pending Review')
                ->count();
        }

        $pendingPayroll = 0;
        if (Schema::hasTable('acc_payroll_integration_batches')) {
            $pendingPayroll = (int) DB::table('acc_payroll_integration_batches')
                ->where('posting_status', 'Pending Review')
                ->count();
        }

        $pendingApprovals = $pendingInventory + $pendingPayroll;

        $sixMonthStart = now()->startOfMonth()->subMonths(5);
        $sixMonthEnd = now()->endOfMonth();

        $trendRawQuery = DB::table('acc_journal_masters as jm')
            ->join('acc_journal_details as jd', 'jm.journal_id', '=', 'jd.journal_id')
            ->join('acc_chart_of_accounts as coa', 'coa.account_id', '=', 'jd.account_id')
            ->whereBetween('jm.journal_date', [$sixMonthStart->toDateString(), $sixMonthEnd->toDateString()])
            ->whereIn('coa.account_type', ['Revenue', 'Expense']);

        JournalQueryScopes::restrictActiveMasters($trendRawQuery, 'jm');

        $trendRaw = $trendRawQuery
            ->groupByRaw("DATE_FORMAT(jm.journal_date, '%Y-%m')")
            ->orderByRaw("DATE_FORMAT(jm.journal_date, '%Y-%m')")
            ->selectRaw("DATE_FORMAT(jm.journal_date, '%Y-%m') as month_key")
            ->selectRaw("COALESCE(SUM(CASE WHEN coa.account_type = 'Revenue' THEN jd.credit_amount - jd.debit_amount ELSE 0 END), 0) as income_total")
            ->selectRaw("COALESCE(SUM(CASE WHEN coa.account_type = 'Expense' THEN jd.debit_amount - jd.credit_amount ELSE 0 END), 0) as expense_total")
            ->get()
            ->keyBy('month_key');

        $trendLabels = [];
        $trendIncome = [];
        $trendExpense = [];

        foreach (CarbonPeriod::create($sixMonthStart, '1 month', $sixMonthEnd->copy()->startOfMonth()) as $month) {
            $key = $month->format('Y-m');
            $point = $trendRaw->get($key);

            $trendLabels[] = $month->format('M Y');
            $trendIncome[] = round((float) ($point->income_total ?? 0), 2);
            $trendExpense[] = round((float) ($point->expense_total ?? 0), 2);
        }

        $leaderboard = DB::table('acc_cost_centers as cc')
            ->join('acc_journal_details as jd', 'cc.cost_center_id', '=', 'jd.cost_center_id')
            ->join('acc_journal_masters as jm', function ($join) {
                JournalQueryScopes::activeMasterOnJoin($join);
            })
            ->join('acc_chart_of_accounts as coa', 'coa.account_id', '=', 'jd.account_id')
            ->whereBetween('jm.journal_date', [$fyStart->toDateString(), $fyEnd->toDateString()])
            ->groupBy('cc.cost_center_id', 'cc.cost_center_name', 'cc.reference_id')
            ->select('cc.cost_center_id', 'cc.cost_center_name', 'cc.reference_id')
            ->selectRaw("COALESCE(SUM(CASE WHEN coa.account_type = 'Revenue' THEN jd.credit_amount - jd.debit_amount ELSE 0 END), 0) as revenue_total")
            ->selectRaw("COALESCE(SUM(CASE WHEN coa.account_type = 'Expense' THEN jd.debit_amount - jd.credit_amount ELSE 0 END), 0) as cost_total")
            ->havingRaw("COALESCE(SUM(CASE WHEN coa.account_type = 'Revenue' THEN jd.credit_amount - jd.debit_amount ELSE 0 END), 0) > 0")
            ->orderByRaw("(COALESCE(SUM(CASE WHEN coa.account_type = 'Revenue' THEN jd.credit_amount - jd.debit_amount ELSE 0 END), 0) - COALESCE(SUM(CASE WHEN coa.account_type = 'Expense' THEN jd.debit_amount - jd.credit_amount ELSE 0 END), 0)) DESC")
            ->limit(5)
            ->get()
            ->map(function ($row) {
                $revenue = (float) $row->revenue_total;
                $cost = (float) $row->cost_total;
                $net = $revenue - $cost;
                $row->net_profit = round($net, 2);
                $row->net_margin_percent = $revenue > 0 ? round(($net / $revenue) * 100, 2) : 0;
                return $row;
            });

        $currentAssets = round((float) DB::table('acc_journal_details as jd')
            ->join('acc_journal_masters as jm', function ($join) {
                JournalQueryScopes::activeMasterOnJoin($join);
            })
            ->join('acc_chart_of_accounts as coa', 'coa.account_id', '=', 'jd.account_id')
            ->where('coa.account_type', 'Asset')
            ->where(function ($query) {
                $query->whereRaw('LOWER(coa.account_name) LIKE ?', ['%cash%'])
                    ->orWhereRaw('LOWER(coa.account_name) LIKE ?', ['%bank%'])
                    ->orWhereRaw('LOWER(coa.account_name) LIKE ?', ['%receivable%'])
                    ->orWhereRaw('LOWER(coa.account_name) LIKE ?', ['%inventory%'])
                    ->orWhereRaw('LOWER(coa.account_name) LIKE ?', ['%stock%'])
                    ->orWhereRaw('LOWER(coa.account_name) LIKE ?', ['%current%']);
            })
            ->selectRaw('COALESCE(SUM(jd.debit_amount) - SUM(jd.credit_amount), 0) as amount')
            ->value('amount'), 2);

        $currentLiabilities = round((float) DB::table('acc_journal_details as jd')
            ->join('acc_journal_masters as jm', function ($join) {
                JournalQueryScopes::activeMasterOnJoin($join);
            })
            ->join('acc_chart_of_accounts as coa', 'coa.account_id', '=', 'jd.account_id')
            ->where('coa.account_type', 'Liability')
            ->where(function ($query) {
                $query->whereRaw('LOWER(coa.account_name) LIKE ?', ['%payable%'])
                    ->orWhereRaw('LOWER(coa.account_name) LIKE ?', ['%accrual%'])
                    ->orWhereRaw('LOWER(coa.account_name) LIKE ?', ['%tax%'])
                    ->orWhereRaw('LOWER(coa.account_name) LIKE ?', ['%short%'])
                    ->orWhereRaw('LOWER(coa.account_name) LIKE ?', ['%current%']);
            })
            ->selectRaw('COALESCE(SUM(jd.credit_amount) - SUM(jd.debit_amount), 0) as amount')
            ->value('amount'), 2);

        $currentRatio = $currentLiabilities > 0 ? round($currentAssets / $currentLiabilities, 2) : 0.0;

        $operatingIncome = round($totalRevenueYtd - $totalExpenseYtd, 2);
        $operatingMarginPercent = $totalRevenueYtd > 0
            ? round(($operatingIncome / $totalRevenueYtd) * 100, 2)
            : 0.0;

        return view('erpaccount::phase4.dashboard.index', [
            'currency' => 'BDT',
            'periodLabel' => $periodLabel,
            'metrics' => [
                'total_assets' => $totalAssets,
                'total_liabilities' => $totalLiabilities,
                'total_equity' => $totalEquity,
                'net_profit_loss_ytd' => $netProfitLossYtd,
                'cash_bank_balance' => $cashAndBankBalance,
                'lc_exposure' => $lcLiabilityExposure,
                'pending_approvals' => $pendingApprovals,
                'pending_inventory' => $pendingInventory,
                'pending_payroll' => $pendingPayroll,
                'total_revenue_ytd' => $totalRevenueYtd,
                'total_expense_ytd' => $totalExpenseYtd,
            ],
            'trend' => [
                'labels' => $trendLabels,
                'income' => $trendIncome,
                'expense' => $trendExpense,
            ],
            'capitalMix' => [
                'assets' => $totalAssets,
                'liabilities' => $totalLiabilities,
                'equity' => $totalEquity,
            ],
            'leaderboard' => $leaderboard,
            'healthRatios' => [
                'current_ratio' => $currentRatio,
                'operating_margin_percent' => $operatingMarginPercent,
                'operating_income' => $operatingIncome,
            ],
        ]);
    }

    private function resolveFinancialYearWindow(): array
    {
        $today = now();

        $period = FinancialPeriod::query()
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->orderByDesc('start_date')
            ->first();

        if ($period !== null) {
            return [
                Carbon::parse($period->start_date)->startOfDay(),
                Carbon::parse($period->end_date)->endOfDay(),
                $period->period_name,
            ];
        }

        $start = $today->copy()->startOfYear();
        $end = $today->copy()->endOfYear();

        return [$start, $end, 'FY ' . $start->format('Y') . '-' . $end->format('Y')];
    }
}
