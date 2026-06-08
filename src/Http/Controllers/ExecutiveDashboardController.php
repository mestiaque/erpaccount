<?php

namespace ME\Erpaccount\Http\Controllers;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use ME\Erpaccount\Models\FinancialPeriod;

class ExecutiveDashboardController extends Controller
{
    public function index(Request $request)
    {
        [$startDate, $endDate, $periodLabel] = $this->resolveFinancialYearWindow();

        $netCashRunway = $this->calculateAssetLikeBalance(['cash', 'bank', 'petty cash', 'cash at bank', 'cash in hand']);
        $accountsReceivable = $this->calculateAssetLikeBalance(['receivable', 'accounts receivable', 'buyer']);
        $accountsPayable = $this->calculateLiabilityLikeBalance(['payable', 'accounts payable', 'supplier', 'creditor']);

        $monthly = DB::table('acc_journal_masters as jm')
            ->join('acc_journal_details as jd', 'jm.journal_id', '=', 'jd.journal_id')
            ->join('acc_chart_of_accounts as coa', 'coa.account_id', '=', 'jd.account_id')
            ->whereBetween('jm.journal_date', [$startDate, $endDate])
            ->whereIn('coa.account_type', ['Revenue', 'Expense'])
            ->groupByRaw("DATE_FORMAT(jm.journal_date, '%Y-%m')")
            ->orderByRaw("DATE_FORMAT(jm.journal_date, '%Y-%m')")
            ->selectRaw("DATE_FORMAT(jm.journal_date, '%Y-%m') as month_key")
            ->selectRaw("COALESCE(SUM(CASE WHEN coa.account_type = 'Revenue' THEN jd.credit_amount - jd.debit_amount ELSE 0 END), 0) as revenue_total")
            ->selectRaw("COALESCE(SUM(CASE WHEN coa.account_type = 'Expense' THEN jd.debit_amount - jd.credit_amount ELSE 0 END), 0) as expense_total")
            ->get()
            ->keyBy('month_key');

        $chartLabels = [];
        $chartRevenue = [];
        $chartExpenses = [];

        foreach (CarbonPeriod::create($startDate->copy()->startOfMonth(), '1 month', $endDate->copy()->startOfMonth()) as $month) {
            $key = $month->format('Y-m');
            $point = $monthly->get($key);

            $chartLabels[] = $month->format('M Y');
            $chartRevenue[] = round((float) ($point->revenue_total ?? 0), 2);
            $chartExpenses[] = round((float) ($point->expense_total ?? 0), 2);
        }

        $labelExpr = "COALESCE(NULLIF(jm.narration, ''), CONCAT('Voucher ', jm.voucher_no))";
        $outstandingExpr = "SUM(CASE WHEN coa.account_type = 'Asset' THEN jd.debit_amount - jd.credit_amount ELSE jd.credit_amount - jd.debit_amount END)";

        $topAlerts = DB::table('acc_journal_masters as jm')
            ->join('acc_journal_details as jd', 'jm.journal_id', '=', 'jd.journal_id')
            ->join('acc_chart_of_accounts as coa', 'coa.account_id', '=', 'jd.account_id')
            ->where(function ($query) {
                $query->where(function ($asset) {
                    $asset->where('coa.account_type', 'Asset')
                        ->where(function ($name) {
                            $name->whereRaw('LOWER(coa.account_name) LIKE ?', ['%receivable%'])
                                ->orWhereRaw('LOWER(coa.account_name) LIKE ?', ['%buyer%'])
                                ->orWhereRaw('LOWER(coa.account_name) LIKE ?', ['%invoice%']);
                        });
                })->orWhere(function ($liability) {
                    $liability->where('coa.account_type', 'Liability')
                        ->where(function ($name) {
                            $name->whereRaw('LOWER(coa.account_name) LIKE ?', ['%payable%'])
                                ->orWhereRaw('LOWER(coa.account_name) LIKE ?', ['%supplier%'])
                                ->orWhereRaw('LOWER(coa.account_name) LIKE ?', ['%creditor%']);
                        });
                });
            })
            ->selectRaw("$labelExpr as alert_label")
            ->selectRaw("$outstandingExpr as outstanding_amount")
            ->groupBy(DB::raw($labelExpr))
            ->havingRaw("ABS($outstandingExpr) > 0.01")
            ->orderByRaw("ABS($outstandingExpr) DESC")
            ->limit(5)
            ->get();

        return view('erpaccount::phase4.dashboard.index', [
            'currency' => 'BDT',
            'periodLabel' => $periodLabel,
            'netCashRunway' => round($netCashRunway, 2),
            'accountsReceivable' => round($accountsReceivable, 2),
            'accountsPayable' => round($accountsPayable, 2),
            'chartLabels' => $chartLabels,
            'chartRevenue' => $chartRevenue,
            'chartExpenses' => $chartExpenses,
            'topAlerts' => $topAlerts,
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

    private function calculateAssetLikeBalance(array $keywords): float
    {
        return (float) DB::table('acc_journal_details as jd')
            ->join('acc_chart_of_accounts as coa', 'coa.account_id', '=', 'jd.account_id')
            ->where('coa.account_type', 'Asset')
            ->where(function ($query) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $query->orWhereRaw('LOWER(coa.account_name) LIKE ?', ['%' . strtolower($keyword) . '%'])
                        ->orWhereRaw('LOWER(coa.account_code) LIKE ?', ['%' . strtolower($keyword) . '%']);
                }
            })
            ->selectRaw('COALESCE(SUM(jd.debit_amount) - SUM(jd.credit_amount), 0) as amount')
            ->value('amount');
    }

    private function calculateLiabilityLikeBalance(array $keywords): float
    {
        return (float) DB::table('acc_journal_details as jd')
            ->join('acc_chart_of_accounts as coa', 'coa.account_id', '=', 'jd.account_id')
            ->where('coa.account_type', 'Liability')
            ->where(function ($query) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $query->orWhereRaw('LOWER(coa.account_name) LIKE ?', ['%' . strtolower($keyword) . '%'])
                        ->orWhereRaw('LOWER(coa.account_code) LIKE ?', ['%' . strtolower($keyword) . '%']);
                }
            })
            ->selectRaw('COALESCE(SUM(jd.credit_amount) - SUM(jd.debit_amount), 0) as amount')
            ->value('amount');
    }
}
