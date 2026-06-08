<?php

namespace ME\Erpaccount\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use ME\Erpaccount\Models\BankAccount;
use ME\Erpaccount\Models\ChartOfAccount;

class BankAccountController extends Controller
{
    public function index(Request $request)
    {
        $bankAccounts = BankAccount::query()
            ->with('chartOfAccount')
            ->orderByDesc('is_active')
            ->orderBy('bank_name')
            ->get();

        $assetLedgerAccounts = ChartOfAccount::query()
            ->where('account_type', 'Asset')
            ->where('is_active', true)
            ->orderBy('account_code')
            ->get();

        $summary = [
            'total_active_banks' => BankAccount::query()->where('is_active', true)->distinct('bank_name')->count('bank_name'),
            'total_accounts' => $bankAccounts->count(),
            'currency_types' => BankAccount::query()->distinct('account_type')->count('account_type'),
            'active_accounts' => BankAccount::query()->where('is_active', true)->count(),
        ];

        if ($this->isApiRequest($request)) {
            return response()->json([
                'data' => [
                    'summary' => $summary,
                    'bank_accounts' => $bankAccounts,
                    'asset_ledgers' => $assetLedgerAccounts,
                ],
            ]);
        }

        return view('erpaccount::phase1.bank_accounts.index', [
            'summary' => $summary,
            'bankAccounts' => $bankAccounts,
            'assetLedgerAccounts' => $assetLedgerAccounts,
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'account_id' => ['required', 'integer', 'exists:acc_chart_of_accounts,account_id'],
            'bank_name' => ['required', 'string', 'max:100'],
            'branch_name' => ['required', 'string', 'max:100'],
            'account_number' => ['required', 'string', 'max:50', 'unique:acc_bank_accounts,account_number'],
            'account_type' => ['required', 'string', 'max:50'],
            'swift_code' => ['nullable', 'string', 'max:20'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $ledger = ChartOfAccount::query()->findOrFail($validated['account_id']);
        if ($ledger->account_type !== 'Asset') {
            return $this->validationError($request, 'Bank account must be mapped to an Asset ledger account.');
        }

        $bankAccount = BankAccount::query()->create([
            'account_id' => $validated['account_id'],
            'bank_name' => $validated['bank_name'],
            'branch_name' => $validated['branch_name'],
            'account_number' => $validated['account_number'],
            'account_type' => $validated['account_type'],
            'swift_code' => $validated['swift_code'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        if ($this->isApiRequest($request)) {
            return response()->json([
                'message' => 'Bank account created successfully.',
                'data' => $bankAccount->load('chartOfAccount'),
            ], 201);
        }

        return redirect()
            ->route('erpaccount.bank-accounts.index')
            ->with('success', 'Bank account created successfully.');
    }

    public function update(Request $request, BankAccount $bankAccount): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'account_id' => ['required', 'integer', 'exists:acc_chart_of_accounts,account_id'],
            'bank_name' => ['required', 'string', 'max:100'],
            'branch_name' => ['required', 'string', 'max:100'],
            'account_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('acc_bank_accounts', 'account_number')->ignore($bankAccount->bank_account_id, 'bank_account_id'),
            ],
            'account_type' => ['required', 'string', 'max:50'],
            'swift_code' => ['nullable', 'string', 'max:20'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $ledger = ChartOfAccount::query()->findOrFail($validated['account_id']);
        if ($ledger->account_type !== 'Asset') {
            return $this->validationError($request, 'Bank account must be mapped to an Asset ledger account.');
        }

        $bankAccount->update([
            'account_id' => $validated['account_id'],
            'bank_name' => $validated['bank_name'],
            'branch_name' => $validated['branch_name'],
            'account_number' => $validated['account_number'],
            'account_type' => $validated['account_type'],
            'swift_code' => $validated['swift_code'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        if ($this->isApiRequest($request)) {
            return response()->json([
                'message' => 'Bank account updated successfully.',
                'data' => $bankAccount->fresh()->load('chartOfAccount'),
            ]);
        }

        return redirect()
            ->route('erpaccount.bank-accounts.index')
            ->with('success', 'Bank account updated successfully.');
    }

    public function destroy(Request $request, BankAccount $bankAccount): JsonResponse|RedirectResponse
    {
        $bankAccount->delete();

        if ($this->isApiRequest($request)) {
            return response()->json([
                'message' => 'Bank account deleted successfully.',
            ]);
        }

        return redirect()
            ->route('erpaccount.bank-accounts.index')
            ->with('success', 'Bank account deleted successfully.');
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
