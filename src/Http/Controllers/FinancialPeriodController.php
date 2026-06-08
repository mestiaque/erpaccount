<?php

namespace ME\Erpaccount\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use ME\Erpaccount\Models\ChartOfAccount;
use ME\Erpaccount\Models\FinancialPeriod;
use ME\Erpaccount\Models\TaxRate;

class FinancialPeriodController extends Controller
{
    public function index(Request $request)
    {
        $periods = FinancialPeriod::query()->orderByDesc('start_date')->get();

        if ($this->isApiRequest($request)) {
            return response()->json([
                'data' => $periods,
            ]);
        }

        return view('erpaccount::phase1.settings.index', [
            'taxRates' => TaxRate::query()->with('chartOfAccount')->orderByDesc('is_active')->orderBy('tax_name')->get(),
            'ledgerAccounts' => ChartOfAccount::query()
                ->whereIn('account_type', ['Liability', 'Expense'])
                ->where('is_active', true)
                ->orderBy('account_type')
                ->orderBy('account_code')
                ->get(),
            'periods' => $periods,
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'period_name' => ['required', 'string', 'max:50', 'unique:acc_financial_periods,period_name'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'is_closed' => ['nullable', 'boolean'],
        ]);

        $period = FinancialPeriod::query()->create([
            'period_name' => $validated['period_name'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'is_closed' => $request->boolean('is_closed'),
        ]);

        if ($this->isApiRequest($request)) {
            return response()->json([
                'message' => 'Financial period created successfully.',
                'data' => $period,
            ], 201);
        }

        return redirect()
            ->route('erpaccount.financial-periods.index')
            ->with('success', 'Financial period created successfully.');
    }

    public function update(Request $request, FinancialPeriod $financialPeriod): JsonResponse|RedirectResponse
    {
        $isToggleOnly = $request->has('is_closed')
            && !$request->has('period_name')
            && !$request->has('start_date')
            && !$request->has('end_date');

        if ($isToggleOnly) {
            $validated = $request->validate([
                'is_closed' => ['required', 'boolean'],
            ]);

            $financialPeriod->update([
                'is_closed' => (bool) $validated['is_closed'],
            ]);
        } else {
            $validated = $request->validate([
                'period_name' => [
                    'required',
                    'string',
                    'max:50',
                    Rule::unique('acc_financial_periods', 'period_name')->ignore($financialPeriod->period_id, 'period_id'),
                ],
                'start_date' => ['required', 'date'],
                'end_date' => ['required', 'date', 'after_or_equal:start_date'],
                'is_closed' => ['nullable', 'boolean'],
            ]);

            $financialPeriod->update([
                'period_name' => $validated['period_name'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'is_closed' => $request->boolean('is_closed'),
            ]);
        }

        if ($this->isApiRequest($request)) {
            return response()->json([
                'message' => 'Financial period updated successfully.',
                'data' => $financialPeriod->fresh(),
            ]);
        }

        return redirect()
            ->route('erpaccount.financial-periods.index')
            ->with('success', 'Financial period updated successfully.');
    }

    private function isApiRequest(Request $request): bool
    {
        return $request->expectsJson() || str_starts_with($request->path(), 'api/');
    }
}
