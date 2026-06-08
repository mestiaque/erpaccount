<?php

namespace ME\Erpaccount\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use ME\Erpaccount\Support\JournalQueryScopes;

class FinancialReportController extends Controller
{
    public function index(Request $request)
    {
        $slug = $request->input('report_type', 'trial_balance');

        return redirect()->route('erpaccount.reports.show', [
            'reportSlug' => in_array($slug, ['trial_balance', 'profit_and_loss', 'balance_sheet'], true) ? $slug : 'trial_balance',
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'as_on_date' => $request->input('end_date'),
        ]);
    }

    public function indexLegacy(Request $request)
    {
        [$startDate, $endDate, $reportType] = $this->resolveFilters($request);

        $payload = $this->buildReportPayload($reportType, $startDate, $endDate);

        return view('erpaccount::phase4.reports.index', [
            'filters' => [
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'report_type' => $reportType,
            ],
            'reportType' => $reportType,
            'report' => $payload,
            'currency' => 'BDT',
        ]);
    }

    public function exportExcel(Request $request)
    {
        return redirect()->route('erpaccount.reports.export-excel', [
            'reportSlug' => $request->input('report_type', 'trial_balance'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'as_on_date' => $request->input('end_date'),
        ]);
    }

    public function exportExcelLegacy(Request $request)
    {
        [$startDate, $endDate, $reportType] = $this->resolveFilters($request);
        $payload = $this->buildReportPayload($reportType, $startDate, $endDate);

        $fileName = 'financial-report-' . $reportType . '-' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($reportType, $payload) {
            $out = fopen('php://output', 'w');

            if ($reportType === 'trial_balance') {
                fputcsv($out, ['Account Code', 'Account Name', 'Type', 'Period Debit', 'Period Credit', 'Closing Balance']);
                foreach ($payload['rows'] as $row) {
                    fputcsv($out, [
                        $row->account_code,
                        $row->account_name,
                        $row->account_type,
                        number_format((float) $row->period_debit, 2, '.', ''),
                        number_format((float) $row->period_credit, 2, '.', ''),
                        number_format((float) $row->closing_balance, 2, '.', ''),
                    ]);
                }
                fputcsv($out, []);
                fputcsv($out, ['TOTAL', '', '', number_format((float) $payload['total_debits'], 2, '.', ''), number_format((float) $payload['total_credits'], 2, '.', ''), '']);
            }

            if ($reportType === 'profit_and_loss') {
                fputcsv($out, ['Account Name', 'Type', 'Amount']);
                foreach ($payload['revenue_rows'] as $row) {
                    fputcsv($out, [$row->account_name, 'Revenue', number_format((float) $row->amount, 2, '.', '')]);
                }
                foreach ($payload['expense_rows'] as $row) {
                    fputcsv($out, [$row->account_name, 'Expense', number_format((float) $row->amount, 2, '.', '')]);
                }
                fputcsv($out, []);
                fputcsv($out, ['Total Revenue', '', number_format((float) $payload['total_revenue'], 2, '.', '')]);
                fputcsv($out, ['Total Expenses', '', number_format((float) $payload['total_expenses'], 2, '.', '')]);
                fputcsv($out, ['Net Profit', '', number_format((float) $payload['net_profit'], 2, '.', '')]);
            }

            if ($reportType === 'balance_sheet') {
                fputcsv($out, ['Account Name', 'Section', 'Amount']);
                foreach ($payload['assets'] as $row) {
                    fputcsv($out, [$row->account_name, $row->balance_group, number_format((float) $row->amount, 2, '.', '')]);
                }
                foreach ($payload['liabilities_equity'] as $row) {
                    fputcsv($out, [$row->account_name, $row->balance_group, number_format((float) $row->amount, 2, '.', '')]);
                }
                fputcsv($out, []);
                fputcsv($out, ['Total Assets', '', number_format((float) $payload['total_assets'], 2, '.', '')]);
                fputcsv($out, ['Total Liabilities + Equity', '', number_format((float) $payload['total_liabilities_equity'], 2, '.', '')]);
            }

            fclose($out);
        }, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function printFriendly(Request $request)
    {
        return redirect()->route('erpaccount.reports.export-pdf', [
            'reportSlug' => $request->input('report_type', 'trial_balance'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'as_on_date' => $request->input('end_date'),
        ]);
    }

    public function printFriendlyLegacy(Request $request)
    {
        [$startDate, $endDate, $reportType] = $this->resolveFilters($request);
        $payload = $this->buildReportPayload($reportType, $startDate, $endDate);

        return view('erpaccount::phase4.reports.print', [
            'reportType' => $reportType,
            'report' => $payload,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'currency' => 'BDT',
        ]);
    }

    private function resolveFilters(Request $request): array
    {
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->input('start_date'))->startOfDay()
            : now()->startOfMonth()->startOfDay();

        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'))->endOfDay()
            : now()->endOfMonth()->endOfDay();

        if ($endDate->lt($startDate)) {
            [$startDate, $endDate] = [$endDate->copy()->startOfDay(), $startDate->copy()->endOfDay()];
        }

        $reportType = $request->input('report_type', 'trial_balance');
        if (!in_array($reportType, ['trial_balance', 'profit_and_loss', 'balance_sheet'], true)) {
            $reportType = 'trial_balance';
        }

        return [$startDate, $endDate, $reportType];
    }

    private function buildReportPayload(string $reportType, Carbon $startDate, Carbon $endDate): array
    {
        if ($reportType === 'profit_and_loss') {
            return $this->profitAndLoss($startDate, $endDate);
        }

        if ($reportType === 'balance_sheet') {
            return $this->balanceSheet($endDate);
        }

        return $this->trialBalance($startDate, $endDate);
    }

    private function trialBalance(Carbon $startDate, Carbon $endDate): array
    {
        $rows = DB::table('acc_chart_of_accounts as coa')
            ->leftJoin('acc_journal_details as jd', 'jd.account_id', '=', 'coa.account_id')
            ->leftJoin('acc_journal_masters as jm', function ($join) {
                JournalQueryScopes::activeMasterOnJoin($join);
            })
            ->where('coa.is_active', true)
            ->groupBy('coa.account_id', 'coa.account_code', 'coa.account_name', 'coa.account_type')
            ->orderBy('coa.account_code')
            ->select([
                'coa.account_id',
                'coa.account_code',
                'coa.account_name',
                'coa.account_type',
            ])
            ->selectRaw("COALESCE(SUM(CASE WHEN jm.journal_date BETWEEN ? AND ? THEN jd.debit_amount ELSE 0 END), 0) as period_debit", [$startDate->toDateString(), $endDate->toDateString()])
            ->selectRaw("COALESCE(SUM(CASE WHEN jm.journal_date BETWEEN ? AND ? THEN jd.credit_amount ELSE 0 END), 0) as period_credit", [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->map(function ($row) {
                $netDebitFirst = in_array($row->account_type, ['Asset', 'Expense'], true);
                $closing = $netDebitFirst
                    ? (float) $row->period_debit - (float) $row->period_credit
                    : (float) $row->period_credit - (float) $row->period_debit;

                $row->closing_balance = round($closing, 2);
                return $row;
            });

        $totalDebits = round((float) $rows->sum('period_debit'), 2);
        $totalCredits = round((float) $rows->sum('period_credit'), 2);

        return [
            'rows' => $rows,
            'total_debits' => $totalDebits,
            'total_credits' => $totalCredits,
            'is_balanced' => abs($totalDebits - $totalCredits) < 0.01,
        ];
    }

    private function profitAndLoss(Carbon $startDate, Carbon $endDate): array
    {
        $rows = DB::table('acc_chart_of_accounts as coa')
            ->join('acc_journal_details as jd', 'jd.account_id', '=', 'coa.account_id')
            ->join('acc_journal_masters as jm', function ($join) {
                JournalQueryScopes::activeMasterOnJoin($join);
            })
            ->whereBetween('jm.journal_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->whereIn('coa.account_type', ['Revenue', 'Expense'])
            ->groupBy('coa.account_id', 'coa.account_name', 'coa.account_type')
            ->orderBy('coa.account_type')
            ->orderBy('coa.account_name')
            ->select([
                'coa.account_id',
                'coa.account_name',
                'coa.account_type',
            ])
            ->selectRaw("COALESCE(SUM(CASE WHEN coa.account_type = 'Revenue' THEN jd.credit_amount - jd.debit_amount ELSE jd.debit_amount - jd.credit_amount END), 0) as amount")
            ->get();

        $revenueRows = $rows->where('account_type', 'Revenue')->values();
        $expenseRows = $rows->where('account_type', 'Expense')->values();

        $totalRevenue = round((float) $revenueRows->sum('amount'), 2);
        $totalExpenses = round((float) $expenseRows->sum('amount'), 2);

        return [
            'revenue_rows' => $revenueRows,
            'expense_rows' => $expenseRows,
            'total_revenue' => $totalRevenue,
            'total_expenses' => $totalExpenses,
            'net_profit' => round($totalRevenue - $totalExpenses, 2),
        ];
    }

    private function balanceSheet(Carbon $endDate): array
    {
        $rows = DB::table('acc_chart_of_accounts as coa')
            ->leftJoin('acc_journal_details as jd', 'jd.account_id', '=', 'coa.account_id')
            ->leftJoin('acc_journal_masters as jm', function ($join) {
                JournalQueryScopes::activeMasterOnJoin($join);
            })
            ->where('coa.is_active', true)
            ->whereIn('coa.account_type', ['Asset', 'Liability', 'Equity'])
            ->groupBy('coa.account_id', 'coa.account_code', 'coa.account_name', 'coa.account_type')
            ->orderBy('coa.account_type')
            ->orderBy('coa.account_code')
            ->select([
                'coa.account_id',
                'coa.account_code',
                'coa.account_name',
                'coa.account_type',
            ])
            ->selectRaw("COALESCE(SUM(CASE WHEN jm.journal_date <= ? THEN jd.debit_amount ELSE 0 END), 0) as total_debit", [$endDate->toDateString()])
            ->selectRaw("COALESCE(SUM(CASE WHEN jm.journal_date <= ? THEN jd.credit_amount ELSE 0 END), 0) as total_credit", [$endDate->toDateString()])
            ->get()
            ->map(function ($row) {
                $amount = $row->account_type === 'Asset'
                    ? (float) $row->total_debit - (float) $row->total_credit
                    : (float) $row->total_credit - (float) $row->total_debit;

                $row->amount = round($amount, 2);
                $row->balance_group = $this->balanceSheetGroup($row->account_type, $row->account_name, $row->account_code);
                return $row;
            })
            ->filter(fn ($row) => abs((float) $row->amount) > 0.009)
            ->values();

        $assets = $rows->where('account_type', 'Asset')->values();
        $liabilitiesEquity = $rows->whereIn('account_type', ['Liability', 'Equity'])->values();

        $totalAssets = round((float) $assets->sum('amount'), 2);
        $totalLiabilitiesEquity = round((float) $liabilitiesEquity->sum('amount'), 2);

        return [
            'assets' => $assets,
            'liabilities_equity' => $liabilitiesEquity,
            'total_assets' => $totalAssets,
            'total_liabilities_equity' => $totalLiabilitiesEquity,
            'is_balanced' => abs($totalAssets - $totalLiabilitiesEquity) < 0.01,
        ];
    }

    private function balanceSheetGroup(string $type, string $name, string $code): string
    {
        $needle = strtolower($name . ' ' . $code);

        if ($type === 'Asset') {
            if (str_contains($needle, 'cash') || str_contains($needle, 'bank') || str_contains($needle, 'receivable') || str_contains($needle, 'inventory')) {
                return 'Current Assets';
            }
            return 'Non-Current Assets';
        }

        if ($type === 'Liability') {
            if (str_contains($needle, 'payable') || str_contains($needle, 'accrual') || str_contains($needle, 'tax')) {
                return 'Current Liabilities';
            }
            return 'Non-Current Liabilities';
        }

        return 'Equity';
    }
}
