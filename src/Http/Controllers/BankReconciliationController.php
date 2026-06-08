<?php

namespace ME\Erpaccount\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use ME\Erpaccount\Http\Requests\BankStatementUploadRequest;
use ME\Erpaccount\Http\Requests\ReconciliationMatchRequest;
use ME\Erpaccount\Models\BankAccount;
use ME\Erpaccount\Models\BankStatementEntry;
use ME\Erpaccount\Models\JournalDetail;

class BankReconciliationController extends Controller
{
    public function index(Request $request)
    {
        $bankAccounts = BankAccount::query()
            ->with('chartOfAccount')
            ->where('is_active', true)
            ->orderBy('bank_name')
            ->get();

        $selectedBankAccountId = (int) ($request->input('bank_account_id') ?: optional($bankAccounts->first())->bank_account_id);
        $fromDate = $request->input('from_date', now()->startOfMonth()->toDateString());
        $toDate = $request->input('to_date', now()->toDateString());

        $selectedBankAccount = $bankAccounts->firstWhere('bank_account_id', $selectedBankAccountId);

        $internalEntries = collect();
        $statementEntries = collect();

        if ($selectedBankAccount !== null) {
            $internalEntries = JournalDetail::query()
                ->with(['journalMaster', 'chartOfAccount'])
                ->where('account_id', $selectedBankAccount->account_id)
                ->whereHas('journalMaster', function ($query) use ($fromDate, $toDate) {
                    $query->whereBetween('journal_date', [$fromDate, $toDate]);
                })
                ->orderByDesc('detail_id')
                ->get();

            $statementEntries = BankStatementEntry::query()
                ->where('bank_account_id', $selectedBankAccount->bank_account_id)
                ->whereBetween('statement_date', [$fromDate, $toDate])
                ->orderByDesc('statement_date')
                ->orderByDesc('statement_id')
                ->get();
        }

        if ($request->expectsJson() || str_starts_with($request->path(), 'api/')) {
            return response()->json([
                'data' => [
                    'bank_accounts' => $bankAccounts,
                    'selected_bank_account_id' => $selectedBankAccountId,
                    'from_date' => $fromDate,
                    'to_date' => $toDate,
                    'internal_entries' => $internalEntries,
                    'statement_entries' => $statementEntries,
                ],
            ]);
        }

        return view('erpaccount::phase2.bank_reconciliation.index', [
            'bankAccounts' => $bankAccounts,
            'selectedBankAccountId' => $selectedBankAccountId,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'internalEntries' => $internalEntries,
            'statementEntries' => $statementEntries,
        ]);
    }

    public function upload(BankStatementUploadRequest $request): JsonResponse|RedirectResponse
    {
        DB::beginTransaction();

        try {
            foreach ($request->input('entries', []) as $entry) {
                BankStatementEntry::query()->create([
                    'bank_account_id' => (int) $request->input('bank_account_id'),
                    'statement_date' => $entry['statement_date'],
                    'reference_no' => $entry['reference_no'] ?? null,
                    'description' => $entry['description'] ?? null,
                    'debit_amount' => round((float) ($entry['debit_amount'] ?? 0), 2),
                    'credit_amount' => round((float) ($entry['credit_amount'] ?? 0), 2),
                    'closing_balance' => isset($entry['closing_balance']) ? round((float) $entry['closing_balance'], 2) : null,
                    'is_reconciled' => false,
                    'reconciled_at' => null,
                    'matched_detail_id' => null,
                ]);
            }

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Statement entries uploaded successfully.',
                ], 201);
            }

            return redirect()
                ->route('erpaccount.bank-reconciliation.index', [
                    'bank_account_id' => $request->input('bank_account_id'),
                    'from_date' => $request->input('from_date'),
                    'to_date' => $request->input('to_date'),
                ])
                ->with('success', 'Statement entries uploaded successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function match(ReconciliationMatchRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $detail = JournalDetail::query()->lockForUpdate()->findOrFail((int) $request->input('detail_id'));
            $statement = BankStatementEntry::query()->lockForUpdate()->findOrFail((int) $request->input('statement_id'));
            $isMatched = (bool) $request->input('is_matched');

            $bankAccount = BankAccount::query()->findOrFail($statement->bank_account_id);
            if ((int) $detail->account_id !== (int) $bankAccount->account_id) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Selected rows are not from the same bank ledger account.',
                ], 422);
            }

            if ($isMatched) {
                $detail->update([
                    'is_reconciled' => true,
                    'reconciled_at' => Carbon::now(),
                    'matched_statement_id' => $statement->statement_id,
                    'reconciliation_note' => 'Matched from reconciliation worksheet',
                ]);

                $statement->update([
                    'is_reconciled' => true,
                    'reconciled_at' => Carbon::now(),
                    'matched_detail_id' => $detail->detail_id,
                ]);
            } else {
                $detail->update([
                    'is_reconciled' => false,
                    'reconciled_at' => null,
                    'matched_statement_id' => null,
                    'reconciliation_note' => null,
                ]);

                $statement->update([
                    'is_reconciled' => false,
                    'reconciled_at' => null,
                    'matched_detail_id' => null,
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Reconciliation updated successfully.',
                'data' => [
                    'detail_id' => $detail->detail_id,
                    'statement_id' => $statement->statement_id,
                    'is_matched' => $isMatched,
                ],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
