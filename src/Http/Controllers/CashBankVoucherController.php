<?php

namespace ME\Erpaccount\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use ME\Erpaccount\Http\Requests\CashBankVoucherStoreRequest;
use ME\Erpaccount\Models\ChartOfAccount;
use ME\Erpaccount\Models\CostCenter;
use ME\Erpaccount\Models\JournalDetail;
use ME\Erpaccount\Models\JournalMaster;
use ME\Erpaccount\Support\VoucherNumberGenerator;

class CashBankVoucherController extends Controller
{
    public function index(Request $request)
    {
        $bankCashLedgers = ChartOfAccount::query()
            ->where('is_active', true)
            ->where('account_type', 'Asset')
            ->where(function ($query) {
                $query->whereRaw('LOWER(account_name) like ?', ['%cash%'])
                    ->orWhereRaw('LOWER(account_name) like ?', ['%bank%'])
                    ->orWhereHas('bankAccounts');
            })
            ->orderBy('account_code')
            ->get();

        $offsetAccounts = ChartOfAccount::query()
            ->where('is_active', true)
            ->orderBy('account_type')
            ->orderBy('account_code')
            ->get();

        $costCenters = CostCenter::query()->orderBy('cost_center_name')->get();

        $accountMeta = $offsetAccounts->mapWithKeys(function (ChartOfAccount $account) {
            $name = strtolower($account->account_name);
            $requiresParty = str_contains($name, 'receivable') || str_contains($name, 'payable');

            return [
                $account->account_id => [
                    'id' => $account->account_id,
                    'label' => $account->account_code . ' - ' . $account->account_name,
                    'requires_cost_center' => in_array($account->account_type, ['Expense', 'Revenue'], true),
                    'requires_party' => $requiresParty,
                ],
            ];
        });

        if ($request->expectsJson() || str_starts_with($request->path(), 'api/')) {
            return response()->json([
                'data' => [
                    'bank_cash_ledgers' => $bankCashLedgers,
                    'offset_accounts' => $offsetAccounts,
                    'cost_centers' => $costCenters,
                    'account_meta' => $accountMeta,
                ],
            ]);
        }

        return view('erpaccount::phase2.cash_bank.index', [
            'bankCashLedgers' => $bankCashLedgers,
            'offsetAccounts' => $offsetAccounts,
            'costCenters' => $costCenters,
            'accountMeta' => $accountMeta,
        ]);
    }

    public function storeReceipt(CashBankVoucherStoreRequest $request): JsonResponse|RedirectResponse
    {
        return $this->storeVoucher($request, 'receipt');
    }

    public function storePayment(CashBankVoucherStoreRequest $request): JsonResponse|RedirectResponse
    {
        return $this->storeVoucher($request, 'payment');
    }

    private function storeVoucher(CashBankVoucherStoreRequest $request, string $type): JsonResponse|RedirectResponse
    {
        if ($request->input('voucher_type') !== $type) {
            return $this->validationError($request, 'Invalid voucher type submitted for this endpoint.');
        }

        $mainAccount = ChartOfAccount::query()->findOrFail((int) $request->input('main_account_id'));

        if (!$this->isCashOrBankLedger($mainAccount)) {
            return $this->validationError($request, 'Main account must be a Cash or Bank ledger account.');
        }

        $rows = $request->input('rows', []);
        $totalAmount = round(collect($rows)->sum(fn ($row) => (float) ($row['amount'] ?? 0)), 2);

        DB::beginTransaction();

        try {
            $voucherPrefix = $type === 'receipt' ? 'RV' : 'PV';
            $voucherNo = VoucherNumberGenerator::next($voucherPrefix);

            $journalMaster = JournalMaster::query()->create([
                'voucher_no' => $voucherNo,
                'journal_date' => $request->input('journal_date'),
                'source_module' => 'Manual',
                'source_reference_id' => null,
                'narration' => $request->input('narration'),
                'created_by' => auth()->id(),
            ]);

            JournalDetail::query()->create([
                'journal_id' => $journalMaster->journal_id,
                'account_id' => $mainAccount->account_id,
                'cost_center_id' => null,
                'party_type' => 'None',
                'party_id' => null,
                'debit_amount' => $type === 'receipt' ? $totalAmount : 0,
                'credit_amount' => $type === 'payment' ? $totalAmount : 0,
            ]);

            foreach ($rows as $row) {
                JournalDetail::query()->create([
                    'journal_id' => $journalMaster->journal_id,
                    'account_id' => (int) $row['account_id'],
                    'cost_center_id' => !empty($row['cost_center_id']) ? (int) $row['cost_center_id'] : null,
                    'party_type' => $row['party_type'] ?? 'None',
                    'party_id' => !empty($row['party_id']) ? (int) $row['party_id'] : null,
                    'debit_amount' => $type === 'payment' ? round((float) $row['amount'], 2) : 0,
                    'credit_amount' => $type === 'receipt' ? round((float) $row['amount'], 2) : 0,
                ]);
            }

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => ucfirst($type) . ' voucher posted successfully.',
                    'data' => [
                        'journal_id' => $journalMaster->journal_id,
                        'voucher_no' => $journalMaster->voucher_no,
                    ],
                ], 201);
            }

            return redirect()
                ->route('erpaccount.cash-bank-vouchers.index')
                ->with('success', ucfirst($type) . ' voucher posted successfully. Voucher No: ' . $journalMaster->voucher_no);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function isCashOrBankLedger(ChartOfAccount $account): bool
    {
        if ($account->account_type !== 'Asset') {
            return false;
        }

        $name = strtolower($account->account_name);
        if (str_contains($name, 'cash') || str_contains($name, 'bank')) {
            return true;
        }

        return $account->bankAccounts()->exists();
    }

    private function validationError(Request $request, string $message): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
            ], 422);
        }

        return redirect()->back()->withErrors(['error' => $message])->withInput();
    }
}
