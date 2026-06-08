<?php

namespace ME\Erpaccount\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use ME\Erpaccount\Models\CostCenter;
use ME\Erpaccount\Support\JournalQueryScopes;

class StyleProfitabilityController extends Controller
{
    public function index(Request $request)
    {
        $costCenters = CostCenter::query()->orderBy('cost_center_name')->get();

        $selectedId = $request->integer('cost_center_id');
        $selectedCostCenter = $selectedId
            ? $costCenters->firstWhere('cost_center_id', $selectedId)
            : null;

        $metrics = [
            'export_revenue' => 0.0,
            'fabric_cost' => 0.0,
            'accessories_cost' => 0.0,
            'cm_labor_cost' => 0.0,
            'total_cost' => 0.0,
            'gross_profit' => 0.0,
            'gross_margin_percent' => 0.0,
        ];

        $recentLines = collect();

        if ($selectedCostCenter !== null) {
            $summary = DB::table('acc_journal_details as jd')
                ->join('acc_journal_masters as jm', function ($join) {
                    JournalQueryScopes::activeMasterOnJoin($join);
                })
                ->join('acc_chart_of_accounts as coa', 'coa.account_id', '=', 'jd.account_id')
                ->where('jd.cost_center_id', $selectedCostCenter->cost_center_id)
                ->selectRaw("COALESCE(SUM(CASE WHEN coa.account_type = 'Revenue' THEN jd.credit_amount - jd.debit_amount ELSE 0 END), 0) as export_revenue")
                ->selectRaw("COALESCE(SUM(CASE WHEN coa.account_type = 'Expense' AND (LOWER(coa.account_name) LIKE '%fabric%' OR LOWER(coa.account_name) LIKE '%yarn%' OR LOWER(coa.account_name) LIKE '%raw material%') THEN jd.debit_amount - jd.credit_amount ELSE 0 END), 0) as fabric_cost")
                ->selectRaw("COALESCE(SUM(CASE WHEN coa.account_type = 'Expense' AND (LOWER(coa.account_name) LIKE '%accessor%' OR LOWER(coa.account_name) LIKE '%trim%' OR LOWER(coa.account_name) LIKE '%packing%' OR LOWER(coa.account_name) LIKE '%label%' OR LOWER(coa.account_name) LIKE '%button%' OR LOWER(coa.account_name) LIKE '%zipper%') THEN jd.debit_amount - jd.credit_amount ELSE 0 END), 0) as accessories_cost")
                ->selectRaw("COALESCE(SUM(CASE WHEN coa.account_type = 'Expense' AND (LOWER(coa.account_name) LIKE '%cm%' OR LOWER(coa.account_name) LIKE '%labor%' OR LOWER(coa.account_name) LIKE '%wage%' OR LOWER(coa.account_name) LIKE '%salary%' OR LOWER(coa.account_name) LIKE '%sewing%' OR LOWER(coa.account_name) LIKE '%production%') THEN jd.debit_amount - jd.credit_amount ELSE 0 END), 0) as cm_labor_cost")
                ->first();

            $metrics['export_revenue'] = round((float) ($summary->export_revenue ?? 0), 2);
            $metrics['fabric_cost'] = round((float) ($summary->fabric_cost ?? 0), 2);
            $metrics['accessories_cost'] = round((float) ($summary->accessories_cost ?? 0), 2);
            $metrics['cm_labor_cost'] = round((float) ($summary->cm_labor_cost ?? 0), 2);
            $metrics['total_cost'] = round($metrics['fabric_cost'] + $metrics['accessories_cost'] + $metrics['cm_labor_cost'], 2);
            $metrics['gross_profit'] = round($metrics['export_revenue'] - $metrics['total_cost'], 2);
            $metrics['gross_margin_percent'] = $metrics['export_revenue'] > 0
                ? round(($metrics['gross_profit'] / $metrics['export_revenue']) * 100, 2)
                : 0;

            $recentLines = DB::table('acc_journal_details as jd')
                ->join('acc_journal_masters as jm', function ($join) {
                    JournalQueryScopes::activeMasterOnJoin($join);
                })
                ->join('acc_chart_of_accounts as coa', 'coa.account_id', '=', 'jd.account_id')
                ->where('jd.cost_center_id', $selectedCostCenter->cost_center_id)
                ->orderByDesc('jm.journal_date')
                ->orderByDesc('jd.detail_id')
                ->limit(10)
                ->select([
                    'jm.journal_date',
                    'jm.voucher_no',
                    'coa.account_name',
                    'coa.account_type',
                    'jd.debit_amount',
                    'jd.credit_amount',
                ])
                ->get();
        }

        return view('erpaccount::phase4.profitability.index', [
            'costCenters' => $costCenters,
            'selectedCostCenter' => $selectedCostCenter,
            'metrics' => $metrics,
            'recentLines' => $recentLines,
            'currency' => 'BDT',
        ]);
    }
}
