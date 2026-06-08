<?php

namespace ME\Erpaccount\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ME\Erpaccount\Http\Requests\JournalVoucherStoreRequest;
use ME\Erpaccount\Models\ChartOfAccount;
use ME\Erpaccount\Models\CostCenter;
use ME\Erpaccount\Models\JournalDetail;
use ME\Erpaccount\Models\JournalMaster;
use ME\Erpaccount\Support\VoucherNumberGenerator;

class JournalVoucherController extends Controller
{
    public function index(Request $request)
    {
        $accounts = ChartOfAccount::query()
            ->where('is_active', true)
            ->orderBy('account_code')
            ->get();

        $costCenters = CostCenter::query()
            ->orderBy('cost_center_type')
            ->orderBy('cost_center_name')
            ->get();

        $accountMeta = $accounts->mapWithKeys(function (ChartOfAccount $account) {
            $name = strtolower($account->account_name);
            $requiresParty = str_contains($name, 'receivable') || str_contains($name, 'payable');
            $preferredPartyType = null;

            if (str_contains($name, 'payable')) {
                $preferredPartyType = 'Supplier';
            } elseif (str_contains($name, 'receivable')) {
                $preferredPartyType = 'Buyer';
            }

            return [
                $account->account_id => [
                    'id' => $account->account_id,
                    'label' => $account->account_code . ' - ' . $account->account_name,
                    'account_type' => $account->account_type,
                    'requires_cost_center' => in_array($account->account_type, ['Expense', 'Revenue'], true),
                    'requires_party' => $requiresParty,
                    'preferred_party_type' => $preferredPartyType,
                ],
            ];
        });

        $recentVouchers = JournalMaster::query()
            ->active()
            ->withSum('details as total_debit', 'debit_amount')
            ->orderByDesc('journal_id')
            ->limit(10)
            ->get();

        if ($request->expectsJson() || str_starts_with($request->path(), 'api/')) {
            return response()->json([
                'data' => [
                    'accounts' => $accounts,
                    'cost_centers' => $costCenters,
                    'account_meta' => $accountMeta,
                    'recent_vouchers' => $recentVouchers,
                ],
            ]);
        }

        return view('erpaccount::phase2.journal_vouchers.index', [
            'accounts' => $accounts,
            'costCenters' => $costCenters,
            'accountMeta' => $accountMeta,
            'recentVouchers' => $recentVouchers,
        ]);
    }

    public function store(JournalVoucherStoreRequest $request): JsonResponse|RedirectResponse
    {
        DB::beginTransaction();

        try {
            $voucherNo = VoucherNumberGenerator::next('JV');

            $journalMaster = JournalMaster::query()->create([
                'voucher_no' => $voucherNo,
                'journal_date' => $request->input('journal_date'),
                'source_module' => 'Manual',
                'source_reference_id' => null,
                'narration' => $request->input('narration'),
                'created_by' => auth()->id(),
            ]);

            foreach ($request->input('rows', []) as $row) {
                JournalDetail::query()->create([
                    'journal_id' => $journalMaster->journal_id,
                    'account_id' => (int) $row['account_id'],
                    'cost_center_id' => !empty($row['cost_center_id']) ? (int) $row['cost_center_id'] : null,
                    'party_type' => $row['party_type'] ?? 'None',
                    'party_id' => !empty($row['party_id']) ? (int) $row['party_id'] : null,
                    'debit_amount' => round((float) $row['debit'], 2),
                    'credit_amount' => round((float) $row['credit'], 2),
                ]);
            }

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Journal voucher posted successfully.',
                    'data' => [
                        'journal_id' => $journalMaster->journal_id,
                        'voucher_no' => $journalMaster->voucher_no,
                    ],
                ], 201);
            }

            return redirect()
                ->route('erpaccount.journal-vouchers.index')
                ->with('success', 'Journal voucher posted successfully. Voucher No: ' . $journalMaster->voucher_no);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function partyOptions(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'party_type' => ['required', 'in:Buyer,Supplier,Employee'],
        ]);

        $options = $this->loadPartyOptions($validated['party_type']);

        return response()->json([
            'data' => $options,
        ]);
    }

    private function loadPartyOptions(string $partyType): array
    {
        $userModelClass = config('auth.providers.users.model');
        if (!is_string($userModelClass) || !class_exists($userModelClass)) {
            return [];
        }

        $model = app($userModelClass);
        if (!$model instanceof Model) {
            return [];
        }

        $query = $userModelClass::query();
        $filterType = strtolower($partyType);

        if (method_exists($model, 'scopeFilterByType')) {
            $query->filterByType($filterType);
        } else {
            $table = $model->getTable();
            if (Schema::hasColumn($table, 'type')) {
                $query->where('type', $filterType);
            } elseif (Schema::hasColumn($table, 'user_type')) {
                $query->where('user_type', $filterType);
            }
        }

        $table = $model->getTable();
        if (Schema::hasColumn($table, 'is_active')) {
            $query->where('is_active', true);
        }

        return $query
            ->orderBy($model->getKeyName())
            ->limit(300)
            ->get()
            ->map(function (Model $user) use ($partyType) {
                $name = $this->resolvePartyDisplayName($user);
                $ledger = $this->resolvePartyLedgerLabel($user, $partyType);

                $label = 'Ledger: ' . ($ledger ?? 'Not Linked') . ' | ' . $name . ' (ID: ' . $user->getKey() . ')';

                return [
                    'id' => (int) $user->getKey(),
                    'name' => $name,
                    'ledger' => $ledger,
                    'display_name' => $label,
                ];
            })
            ->values()
            ->all();
    }

    private function resolvePartyDisplayName(Model $user): string
    {
        $candidates = [
            data_get($user, 'name'),
            data_get($user, 'full_name'),
            data_get($user, 'company_name'),
            data_get($user, 'display_name'),
            data_get($user, 'username'),
        ];

        foreach ($candidates as $value) {
            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        return 'User-' . $user->getKey();
    }

    private function resolvePartyLedgerLabel(Model $user, ?string $partyType = null): ?string
    {
        $linkedAccountId = data_get($user, 'account_id');
        if (!empty($linkedAccountId)) {
            $ledger = ChartOfAccount::query()
                ->where('account_id', (int) $linkedAccountId)
                ->select(['account_code', 'account_name'])
                ->first();

            if ($ledger) {
                return trim($ledger->account_code) . ' - ' . trim($ledger->account_name);
            }
        }

        $candidates = [
            data_get($user, 'ledger_name'),
            data_get($user, 'party_ledger_name'),
            data_get($user, 'account_name'),
            data_get($user, 'ledger_code'),
            data_get($user, 'account_code'),
        ];

        foreach ($candidates as $value) {
            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        $coaCode = data_get($user, 'account_code');
        $coaName = data_get($user, 'account_name');
        if (is_string($coaCode) && trim($coaCode) !== '' && is_string($coaName) && trim($coaName) !== '') {
            return trim($coaCode) . ' - ' . trim($coaName);
        }

        $defaultLedger = $this->defaultLedgerByPartyType($partyType);
        if ($defaultLedger !== null) {
            return $defaultLedger;
        }

        return null;
    }

    private function defaultLedgerByPartyType(?string $partyType): ?string
    {
        if (!is_string($partyType) || trim($partyType) === '') {
            return null;
        }

        static $cache = [];
        $type = strtolower(trim($partyType));

        if (array_key_exists($type, $cache)) {
            return $cache[$type];
        }

        $query = ChartOfAccount::query()->where('is_active', true);

        if ($type === 'supplier') {
            $query->whereRaw('LOWER(account_name) like ?', ['%payable%']);
        } elseif ($type === 'buyer') {
            $query->whereRaw('LOWER(account_name) like ?', ['%receivable%']);
        } elseif ($type === 'employee') {
            $query->where(function ($q) {
                $q->whereRaw('LOWER(account_name) like ?', ['%employee%'])
                    ->orWhereRaw('LOWER(account_name) like ?', ['%salary%'])
                    ->orWhereRaw('LOWER(account_name) like ?', ['%payable%']);
            });
        } else {
            $cache[$type] = null;
            return null;
        }

        $ledger = $query
            ->orderBy('account_code')
            ->select(['account_code', 'account_name'])
            ->first();

        $cache[$type] = $ledger
            ? trim($ledger->account_code) . ' - ' . trim($ledger->account_name)
            : null;

        return $cache[$type];
    }
}
