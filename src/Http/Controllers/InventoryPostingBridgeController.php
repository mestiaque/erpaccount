<?php

namespace ME\Erpaccount\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use ME\Erpaccount\Http\Requests\ManualInventoryJournalRequest;
use ME\Erpaccount\Models\ChartOfAccount;
use ME\Erpaccount\Models\CostCenter;
use ME\Erpaccount\Models\JournalDetail;
use ME\Erpaccount\Models\JournalMaster;
use ME\Erpaccount\Support\VoucherNumberGenerator;

class InventoryPostingBridgeController extends Controller
{
    public function index(Request $request)
    {
        $costCenters = CostCenter::query()->orderBy('cost_center_name')->get();

        $transactionTypes = [
            'Material Purchase',
            'Issue to Production (WIP)',
            'Inventory Adjustment/Loss',
        ];

        $currencies = ['BDT', 'USD'];

        return view('erpaccount::phase3.inventory.index', [
            'costCenters' => $costCenters,
            'transactionTypes' => $transactionTypes,
            'currencies' => $currencies,
        ]);
    }

    public function store(ManualInventoryJournalRequest $request): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $amount = round((float) $request->input('amount'), 2);

            if ($amount <= 0) {
                DB::rollBack();
                return redirect()->back()->withErrors(['amount' => 'Please enter a valid amount.'])->withInput();
            }

            [$debitLedger, $creditLedger] = $this->resolveLedgersForTransaction($request->input('transaction_type'));

            if ($debitLedger === null || $creditLedger === null) {
                DB::rollBack();
                return redirect()->back()->withErrors(['transaction_type' => 'Required ledger accounts were not found for this transaction type.'])->withInput();
            }

            $costCenterId = $request->filled('cost_center_id') ? (int) $request->input('cost_center_id') : null;
            $debitCostCenterId = $request->input('transaction_type') === 'Issue to Production (WIP)' ? $costCenterId : null;

            $journal = JournalMaster::query()->create([
                'voucher_no' => VoucherNumberGenerator::next('IM'),
                'journal_date' => $request->input('entry_date'),
                'source_module' => 'Inventory',
                'source_reference_id' => null,
                'narration' => $this->buildNarration($request->input('transaction_type'), $request->input('currency'), $request->input('remarks')),
                'created_by' => auth()->id(),
            ]);

            JournalDetail::query()->create([
                'journal_id' => $journal->journal_id,
                'account_id' => $debitLedger->account_id,
                'cost_center_id' => $debitCostCenterId,
                'party_type' => 'None',
                'party_id' => null,
                'debit_amount' => $amount,
                'credit_amount' => 0,
            ]);

            JournalDetail::query()->create([
                'journal_id' => $journal->journal_id,
                'account_id' => $creditLedger->account_id,
                'cost_center_id' => null,
                'party_type' => 'None',
                'party_id' => null,
                'debit_amount' => 0,
                'credit_amount' => $amount,
            ]);

            DB::commit();

            return redirect()
                ->route('erpaccount.manual-inventory.index')
                ->with('success', 'Inventory financial journal posted successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function resolveLedgersForTransaction(string $transactionType): array
    {
        return match ($transactionType) {
            'Material Purchase' => [
                $this->resolveLedger(['raw material inventory', 'raw material stock', 'materials inventory'], ['Asset']),
                $this->resolveLedger(['supplier payable', 'accounts payable', 'trade payable', 'payable'], ['Liability']),
            ],
            'Issue to Production (WIP)' => [
                $this->resolveLedger(['work in process', 'wip', 'production in process'], ['Asset', 'Expense']),
                $this->resolveLedger(['raw material inventory', 'raw material stock', 'materials inventory'], ['Asset']),
            ],
            'Inventory Adjustment/Loss' => [
                $this->resolveLedger(['inventory loss', 'stock loss', 'inventory adjustment loss', 'shrinkage loss'], ['Expense']),
                $this->resolveLedger(['raw material inventory', 'raw material stock', 'materials inventory'], ['Asset']),
            ],
            default => [null, null],
        };
    }

    private function resolveLedger(array $nameKeywords, array $types)
    {
        $query = ChartOfAccount::query()->where('is_active', true);

        $query->where(function ($builder) use ($nameKeywords) {
            foreach ($nameKeywords as $keyword) {
                $builder->orWhereRaw('LOWER(account_name) like ?', ['%' . strtolower($keyword) . '%']);
            }
        });

        if (!empty($types)) {
            $query->whereIn('account_type', $types);
        }

        return $query->orderBy('account_code')->first();
    }

    private function buildNarration(string $transactionType, string $currency, string $remarks): string
    {
        $parts = array_filter([
            'Inventory manual journal',
            $transactionType,
            strtoupper($currency),
            $remarks,
        ]);

        return implode(' | ', $parts);
    }
}
