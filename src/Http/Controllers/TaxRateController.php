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

class TaxRateController extends Controller
{
    public function index(Request $request)
    {
        $taxRates = TaxRate::query()
            ->with('chartOfAccount')
            ->orderByDesc('is_active')
            ->orderBy('tax_name')
            ->get();

        $ledgerAccounts = ChartOfAccount::query()
            ->whereIn('account_type', ['Liability', 'Expense'])
            ->where('is_active', true)
            ->orderBy('account_type')
            ->orderBy('account_code')
            ->get();

        $periods = FinancialPeriod::query()->orderByDesc('start_date')->get();

        if ($this->isApiRequest($request)) {
            return response()->json([
                'data' => [
                    'tax_rates' => $taxRates,
                    'ledger_accounts' => $ledgerAccounts,
                    'financial_periods' => $periods,
                ],
            ]);
        }

        return view('erpaccount::phase1.settings.index', [
            'taxRates' => $taxRates,
            'ledgerAccounts' => $ledgerAccounts,
            'periods' => $periods,
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'tax_name' => ['required', 'string', 'max:50', 'unique:acc_tax_rates,tax_name'],
            'percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'ledger_account_id' => ['required', 'integer', 'exists:acc_chart_of_accounts,account_id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $ledgerAccount = ChartOfAccount::query()->findOrFail($validated['ledger_account_id']);
        if (!in_array($ledgerAccount->account_type, ['Liability', 'Expense'], true)) {
            return $this->validationError($request, 'Tax ledger account must be Liability or Expense type.');
        }

        $taxRate = TaxRate::query()->create([
            'tax_name' => $validated['tax_name'],
            'percentage' => $validated['percentage'],
            'ledger_account_id' => $validated['ledger_account_id'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        if ($this->isApiRequest($request)) {
            return response()->json([
                'message' => 'Tax rate created successfully.',
                'data' => $taxRate->load('chartOfAccount'),
            ], 201);
        }

        return redirect()
            ->route('erpaccount.tax-rates.index')
            ->with('success', 'Tax rate created successfully.');
    }

    public function update(Request $request, TaxRate $taxRate): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'tax_name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('acc_tax_rates', 'tax_name')->ignore($taxRate->tax_rate_id, 'tax_rate_id'),
            ],
            'percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'ledger_account_id' => ['required', 'integer', 'exists:acc_chart_of_accounts,account_id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $ledgerAccount = ChartOfAccount::query()->findOrFail($validated['ledger_account_id']);
        if (!in_array($ledgerAccount->account_type, ['Liability', 'Expense'], true)) {
            return $this->validationError($request, 'Tax ledger account must be Liability or Expense type.');
        }

        $taxRate->update([
            'tax_name' => $validated['tax_name'],
            'percentage' => $validated['percentage'],
            'ledger_account_id' => $validated['ledger_account_id'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        if ($this->isApiRequest($request)) {
            return response()->json([
                'message' => 'Tax rate updated successfully.',
                'data' => $taxRate->fresh()->load('chartOfAccount'),
            ]);
        }

        return redirect()
            ->route('erpaccount.tax-rates.index')
            ->with('success', 'Tax rate updated successfully.');
    }

    private function isApiRequest(Request $request): bool
    {
        return $request->expectsJson() || str_starts_with($request->path(), 'api/');
    }

    private function validationError(Request $request, string $message): JsonResponse|RedirectResponse
    {
        if ($this->isApiRequest($request)) {
            return response()->json([
                'message' => $message,
            ], 422);
        }

        return redirect()->back()->withErrors(['error' => $message])->withInput();
    }
}
