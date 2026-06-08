<?php

namespace ME\Erpaccount\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use ME\Erpaccount\Http\Requests\LcFinancialSyncRequest;
use ME\Erpaccount\Models\LcFinancial;

class CommercialLcTrackerController extends Controller
{
    public function index(Request $request)
    {
        $lcFinancials = LcFinancial::query()
            ->orderByDesc('lc_finance_id')
            ->get();

        $summary = [
            'master_lc_count' => $lcFinancials->where('lc_type', 'Master_LC')->count(),
            'b2b_lc_count' => $lcFinancials->where('lc_type', 'Back_To_Back_LC')->count(),
            'total_margin_used' => $lcFinancials->sum('bank_margin_used'),
            'total_margin_limit' => $lcFinancials->sum('bank_margin_limit'),
            'total_commission_paid' => $lcFinancials->sum('bank_commission_paid'),
            'total_acceptance_cost' => $lcFinancials->sum('acceptance_cost_paid'),
            'total_liability' => $lcFinancials->sum('outstanding_liability'),
            'total_clearing_cost' => $lcFinancials->sum('customs_clearing_cost'),
            'total_freight_cost' => $lcFinancials->sum('freight_cost'),
        ];

        $masterLcs = $lcFinancials->where('lc_type', 'Master_LC')->values();
        $b2bLcs = $lcFinancials->where('lc_type', 'Back_To_Back_LC')->values();

        if ($request->expectsJson() || str_starts_with($request->path(), 'api/')) {
            return response()->json([
                'data' => compact('summary', 'masterLcs', 'b2bLcs'),
            ]);
        }

        return view('erpaccount::phase3.commercial.index', [
            'summary' => $summary,
            'masterLcs' => $masterLcs,
            'b2bLcs' => $b2bLcs,
        ]);
    }

    public function sync(LcFinancialSyncRequest $request): JsonResponse
    {
        $lcFinancial = LcFinancial::query()->updateOrCreate(
            [
                'lc_type' => $request->input('lc_type'),
                'lc_id_reference' => $request->input('lc_id_reference'),
            ],
            [
                'total_lc_value' => $request->input('total_lc_value'),
                'currency' => $request->input('currency'),
                'exchange_rate' => $request->input('exchange_rate'),
                'bank_margin_percentage' => $request->input('bank_margin_percentage'),
                'bank_margin_limit' => $request->input('bank_margin_limit', 0),
                'bank_margin_used' => $request->input('bank_margin_used', 0),
                'bank_commission_paid' => $request->input('bank_commission_paid', 0),
                'acceptance_cost_paid' => $request->input('acceptance_cost_paid', 0),
                'outstanding_liability' => $request->input('outstanding_liability', 0),
                'customs_clearing_cost' => $request->input('customs_clearing_cost', 0),
                'freight_cost' => $request->input('freight_cost', 0),
                'posting_status' => 'Active',
            ]
        );

        return response()->json([
            'message' => 'LC financial data synced successfully.',
            'data' => $lcFinancial,
        ], 201);
    }
}
