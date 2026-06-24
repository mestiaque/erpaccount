<?php

namespace ME\Erpaccount\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use ME\Erpaccount\Http\Requests\JournalVoucherStoreRequest;
use ME\Erpaccount\Models\ChartOfAccount;
use ME\Erpaccount\Models\CostCenter;
use ME\Erpaccount\Models\Creditor;
use ME\Erpaccount\Models\Debitor;
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
        if ($partyType === 'Supplier') {
            return Creditor::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get()
                ->map(fn (Creditor $c) => [
                    'id'           => $c->creditor_id,
                    'name'         => $c->name,
                    'display_name' => 'CRD-' . str_pad((string) $c->creditor_id, 4, '0', STR_PAD_LEFT) . ' | ' . $c->name,
                ])
                ->values()
                ->all();
        }

        if ($partyType === 'Buyer') {
            return Debitor::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get()
                ->map(fn (Debitor $d) => [
                    'id'           => $d->debitor_id,
                    'name'         => $d->name,
                    'display_name' => 'DBT-' . str_pad((string) $d->debitor_id, 4, '0', STR_PAD_LEFT) . ' | ' . $d->name,
                ])
                ->values()
                ->all();
        }

        if ($partyType === 'Employee') {
            return $this->loadEmployeeOptions();
        }

        return [];
    }

    private function loadEmployeeOptions(): array
    {
        $class = 'ME\\Hr\\Models\\HrEmployee';
        if (!class_exists($class)) {
            return [];
        }

        return $class::query()
            ->orderBy('employee_id')
            ->limit(500)
            ->get()
            ->map(function ($emp) {
                $name = $this->empStr($emp->name ?? $emp->full_name ?? null)
                        ?? ('Employee-' . $emp->id);

                $parts = array_values(array_filter([
                    $this->empStr($emp->employee_id   ?? null),
                    $name,
                    $this->empStr($emp->designation   ?? null),
                    $this->empStr($emp->department    ?? null),
                    $this->empStr($emp->section       ?? null),
                    $this->empStr($emp->subsection    ?? null),
                ]));

                return [
                    'id'           => $emp->id,
                    'name'         => $name,
                    'display_name' => implode(' | ', $parts),
                ];
            })
            ->values()
            ->all();
    }

    private function empStr(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_bool($value)) {
            return null;
        }
        if (is_string($value)) {
            $v = trim($value);
            if ($v === '') return null;
            // JSON-cast string like {"id":3,"name":"Production"} — extract name
            if ($v[0] === '{' || $v[0] === '[') {
                $decoded = json_decode($v, true);
                if (is_array($decoded)) {
                    return $this->empStr($decoded['name'] ?? $decoded['title'] ?? null);
                }
            }
            return $v;
        }
        if (is_numeric($value)) {
            return (string) $value;
        }
        if (is_array($value)) {
            return $this->empStr($value['name'] ?? $value['title'] ?? null);
        }
        if (is_object($value)) {
            return $this->empStr($value->name ?? $value->title ?? null);
        }
        return null;
    }
}
