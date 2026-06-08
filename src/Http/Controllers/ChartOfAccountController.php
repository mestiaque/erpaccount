<?php

namespace ME\Erpaccount\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use ME\Erpaccount\Models\ChartOfAccount;

class ChartOfAccountController extends Controller
{
    private const ACCOUNT_TYPES = ['Asset', 'Liability', 'Equity', 'Revenue', 'Expense'];

    public function index(Request $request)
    {
        $rootNodes = ChartOfAccount::query()
            ->whereNull('parent_id')
            ->with('childrenRecursive')
            ->orderBy('account_type')
            ->orderBy('account_code')
            ->get();

        $accounts = ChartOfAccount::query()
            ->orderBy('account_type')
            ->orderBy('account_code')
            ->get();

        if ($this->isApiRequest($request)) {
            return response()->json([
                'data' => [
                    'account_types' => self::ACCOUNT_TYPES,
                    'tree' => $rootNodes,
                ],
            ]);
        }

        return view('erpaccount::phase1.chart_of_accounts.index', [
            'accountTypes' => self::ACCOUNT_TYPES,
            'treeByType' => $rootNodes->groupBy('account_type'),
            'accounts' => $accounts,
            'accountsByType' => $accounts->groupBy('account_type')->map(
                fn ($typeAccounts) => $typeAccounts->map(fn (ChartOfAccount $item) => [
                    'account_id' => $item->account_id,
                    'account_code' => $item->account_code,
                    'account_name' => $item->account_name,
                    'parent_id' => $item->parent_id,
                ])->values()
            ),
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'account_code' => ['required', 'string', 'max:50', 'unique:acc_chart_of_accounts,account_code'],
            'account_name' => ['required', 'string', 'max:150'],
            'parent_id' => ['nullable', 'integer', 'exists:acc_chart_of_accounts,account_id'],
            'account_type' => ['required', Rule::in(self::ACCOUNT_TYPES)],
            'is_reconcilable' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if (!empty($validated['parent_id'])) {
            $parent = ChartOfAccount::query()->findOrFail($validated['parent_id']);
            if ($parent->account_type !== $validated['account_type']) {
                return $this->validationError($request, 'Parent account must belong to the same account type.');
            }
        }

        $account = ChartOfAccount::query()->create([
            'account_code' => $validated['account_code'],
            'account_name' => $validated['account_name'],
            'parent_id' => $validated['parent_id'] ?? null,
            'account_type' => $validated['account_type'],
            'is_reconcilable' => $request->boolean('is_reconcilable'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        if ($this->isApiRequest($request)) {
            return response()->json([
                'message' => 'Account created successfully.',
                'data' => $account,
            ], 201);
        }

        return redirect()
            ->route('erpaccount.chart-of-accounts.index')
            ->with('success', 'Account created successfully.');
    }

    public function update(Request $request, ChartOfAccount $chartOfAccount): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'account_code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('acc_chart_of_accounts', 'account_code')->ignore($chartOfAccount->account_id, 'account_id'),
            ],
            'account_name' => ['required', 'string', 'max:150'],
            'parent_id' => ['nullable', 'integer', 'exists:acc_chart_of_accounts,account_id'],
            'account_type' => ['required', Rule::in(self::ACCOUNT_TYPES)],
            'is_reconcilable' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if (!empty($validated['parent_id'])) {
            $parentId = (int) $validated['parent_id'];

            if ($parentId === (int) $chartOfAccount->account_id) {
                return $this->validationError($request, 'An account cannot be its own parent.');
            }

            if ($this->wouldCreateCycle($chartOfAccount->account_id, $parentId)) {
                return $this->validationError($request, 'Invalid parent selection. This creates a hierarchy cycle.');
            }

            $parent = ChartOfAccount::query()->findOrFail($parentId);
            if ($parent->account_type !== $validated['account_type']) {
                return $this->validationError($request, 'Parent account must belong to the same account type.');
            }
        }

        $chartOfAccount->update([
            'account_code' => $validated['account_code'],
            'account_name' => $validated['account_name'],
            'parent_id' => $validated['parent_id'] ?? null,
            'account_type' => $validated['account_type'],
            'is_reconcilable' => $request->boolean('is_reconcilable'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        if ($this->isApiRequest($request)) {
            return response()->json([
                'message' => 'Account updated successfully.',
                'data' => $chartOfAccount->fresh(),
            ]);
        }

        return redirect()
            ->route('erpaccount.chart-of-accounts.index')
            ->with('success', 'Account updated successfully.');
    }

    public function destroy(Request $request, ChartOfAccount $chartOfAccount): JsonResponse|RedirectResponse
    {
        $chartOfAccount->loadCount(['children', 'bankAccounts', 'taxRates']);

        if ($chartOfAccount->children_count > 0 || $chartOfAccount->bank_accounts_count > 0 || $chartOfAccount->tax_rates_count > 0) {
            return $this->validationError($request, 'Cannot delete account because it is referenced by child accounts or transactions setup.');
        }

        $chartOfAccount->delete();

        if ($this->isApiRequest($request)) {
            return response()->json([
                'message' => 'Account deleted successfully.',
            ]);
        }

        return redirect()
            ->route('erpaccount.chart-of-accounts.index')
            ->with('success', 'Account deleted successfully.');
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

    private function wouldCreateCycle(int $currentAccountId, int $candidateParentId): bool
    {
        $cursorId = $candidateParentId;

        while ($cursorId !== null) {
            if ($cursorId === $currentAccountId) {
                return true;
            }

            $cursorId = ChartOfAccount::query()
                ->where('account_id', $cursorId)
                ->value('parent_id');
        }

        return false;
    }
}
