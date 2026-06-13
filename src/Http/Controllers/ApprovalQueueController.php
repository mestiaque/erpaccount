<?php

namespace ME\Erpaccount\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ME\Erpaccount\Models\ChartOfAccount;
use ME\Erpaccount\Models\CostCenter;
use ME\Erpaccount\Models\JournalDetail;
use ME\Erpaccount\Models\JournalMaster;
use ME\Erpaccount\Support\VoucherNumberGenerator;

class ApprovalQueueController extends Controller
{
    public function index()
    {
        $pendingInventory = collect();
        if (Schema::hasTable('acc_inventory_posting_logs')) {
            $pendingInventory = DB::table('acc_inventory_posting_logs')
                ->where('posting_status', 'Pending Review')
                ->orderByDesc('created_at')
                ->get()
                ->map(function ($row) {
                    $row->decoded_payload = json_decode($row->payload, true) ?: [];
                    return $row;
                });
        }

        $pendingPayroll = collect();
        if (Schema::hasTable('acc_payroll_integration_batches')) {
            $pendingPayroll = DB::table('acc_payroll_integration_batches')
                ->where('posting_status', 'Pending Review')
                ->orderByDesc('created_at')
                ->get()
                ->map(function ($row) {
                    $row->decoded_payload = json_decode($row->payload, true) ?: [];
                    return $row;
                });
        }

        return view('erpaccount::phase4.approvals.index', [
            'pendingInventory' => $pendingInventory,
            'pendingPayroll' => $pendingPayroll,
        ]);
    }

    public function approveInventory(Request $request, $logId): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $log = DB::table('acc_inventory_posting_logs')
                ->where('inventory_log_id', $logId)
                ->where('posting_status', 'Pending Review')
                ->first();

            if ($log === null) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Inventory log not found or already posted.');
            }

            $valuation = round((float) ($log->override_valuation ?? $log->system_valuation), 2);
            if ($valuation <= 0) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Valuation must be greater than zero.');
            }

            [$debitLedger, $creditLedger, $isIssue] = $this->resolveInventoryLedgers($log->transaction_type, $log->description);

            if ($debitLedger === null || $creditLedger === null) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Required inventory ledger accounts were not found in the COA. Please check your accounts setup.');
            }

            $payload = json_decode($log->payload, true) ?: [];
            $costCenterId = null;

            if ($isIssue && isset($payload['line'])) {
                $line = strtoupper($payload['line']);
                $costCenter = CostCenter::query()
                    ->whereRaw('LOWER(cost_center_name) LIKE ?', ['%line ' . strtolower($line) . '%'])
                    ->first();
                if ($costCenter !== null) {
                    $costCenterId = $costCenter->cost_center_id;
                }
            }

            $journal = JournalMaster::query()->create([
                'voucher_no' => VoucherNumberGenerator::next('IM'),
                'journal_date' => Carbon::parse($log->created_at)->toDateString(),
                'source_module' => 'Inventory',
                'source_reference_id' => $log->inventory_log_id,
                'narration' => ($log->description ?: 'Inventory posting') . ' | Ref: ' . $log->reference_no,
                'created_by' => auth()->id(),
            ]);

            JournalDetail::query()->create([
                'journal_id' => $journal->journal_id,
                'account_id' => $debitLedger->account_id,
                'cost_center_id' => $costCenterId,
                'party_type' => 'None',
                'party_id' => null,
                'debit_amount' => $valuation,
                'credit_amount' => 0,
            ]);

            JournalDetail::query()->create([
                'journal_id' => $journal->journal_id,
                'account_id' => $creditLedger->account_id,
                'cost_center_id' => null,
                'party_type' => 'None',
                'party_id' => null,
                'debit_amount' => 0,
                'credit_amount' => $valuation,
            ]);

            DB::table('acc_inventory_posting_logs')
                ->where('inventory_log_id', $logId)
                ->update([
                    'posting_status' => 'Posted',
                    'journal_id' => $journal->journal_id,
                    'reviewed_by' => auth()->id(),
                    'reviewed_at' => now(),
                    'updated_at' => now(),
                ]);

            DB::commit();

            return redirect()
                ->route('erpaccount.approvals.index')
                ->with('success', 'Inventory journal posted successfully as ' . $journal->voucher_no . '.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to approve inventory log: ' . $e->getMessage());
        }
    }

    public function approvePayroll(Request $request, $batchId): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $batch = DB::table('acc_payroll_integration_batches')
                ->where('payroll_batch_id', $batchId)
                ->where('posting_status', 'Pending Review')
                ->first();

            if ($batch === null) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Payroll batch not found or already posted.');
            }

            $salaryExpense = $this->resolveLedger(['salary expense', 'wages expense', 'payroll expense'], ['Expense']);
            $pfLiability = $this->resolveLedger(['provident fund liability', 'pf liability', 'provident fund payable'], ['Liability']);
            $advanceAsset = $this->resolveLedger(['advance salary', 'salary advance', 'advance salary asset'], ['Asset']);
            $salaryPayable = $this->resolveLedger(['salary payable', 'wages payable'], ['Liability']);

            // Fallback for payable keyword since it is broad
            if ($salaryPayable === null) {
                $salaryPayable = $this->resolveLedger(['payable'], ['Liability']);
            }

            if ($salaryExpense === null || $pfLiability === null || $advanceAsset === null || $salaryPayable === null) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Required payroll ledger accounts (Salary Expense, PF Liability, Advance Salary, or Salary Payable) were not found in the COA.');
            }

            $basic = round((float) $batch->total_basic, 2);
            $allowances = round((float) $batch->total_allowances, 2);
            $overtime = round((float) $batch->total_overtime, 2);
            $pfDeduction = round((float) $batch->total_pf_deduction, 2);
            $advanceAdjusted = round((float) $batch->total_advance_adjusted, 2);
            $netPayable = round((float) $batch->net_payable, 2);

            $debitTotal = round($basic + $allowances + $overtime, 2);
            $creditTotal = round($pfDeduction + $advanceAdjusted + $netPayable, 2);

            if ($debitTotal !== $creditTotal) {
                DB::rollBack();
                return redirect()->back()->with('error', 'The payroll batch does not balance (Debits: ' . $debitTotal . ', Credits: ' . $creditTotal . ').');
            }

            $journal = JournalMaster::query()->create([
                'voucher_no' => VoucherNumberGenerator::next('JV'),
                'journal_date' => Carbon::parse($batch->payroll_month . '-01')->endOfMonth()->toDateString(),
                'source_module' => 'Payroll',
                'source_reference_id' => $batch->payroll_batch_id,
                'narration' => $batch->summary_label ?: ('Manual payroll voucher for ' . $batch->payroll_month),
                'created_by' => auth()->id(),
            ]);

            // 1. Debit Salary Expense
            JournalDetail::query()->create([
                'journal_id' => $journal->journal_id,
                'account_id' => $salaryExpense->account_id,
                'cost_center_id' => null,
                'party_type' => 'None',
                'party_id' => null,
                'debit_amount' => $debitTotal,
                'credit_amount' => 0,
            ]);

            // 2. Credit PF Liability
            if ($pfDeduction > 0) {
                JournalDetail::query()->create([
                    'journal_id' => $journal->journal_id,
                    'account_id' => $pfLiability->account_id,
                    'cost_center_id' => null,
                    'party_type' => 'None',
                    'party_id' => null,
                    'debit_amount' => 0,
                    'credit_amount' => $pfDeduction,
                ]);
            }

            // 3. Credit Advance Salary
            if ($advanceAdjusted > 0) {
                JournalDetail::query()->create([
                    'journal_id' => $journal->journal_id,
                    'account_id' => $advanceAsset->account_id,
                    'cost_center_id' => null,
                    'party_type' => 'None',
                    'party_id' => null,
                    'debit_amount' => 0,
                    'credit_amount' => $advanceAdjusted,
                ]);
            }

            // 4. Credit Salary Payable
            JournalDetail::query()->create([
                'journal_id' => $journal->journal_id,
                'account_id' => $salaryPayable->account_id,
                'cost_center_id' => null,
                'party_type' => 'None',
                'party_id' => null,
                'debit_amount' => 0,
                'credit_amount' => $netPayable,
            ]);

            DB::table('acc_payroll_integration_batches')
                ->where('payroll_batch_id', $batchId)
                ->update([
                    'posting_status' => 'Posted',
                    'journal_id' => $journal->journal_id,
                    'posted_at' => now(),
                    'updated_at' => now(),
                ]);

            DB::commit();

            return redirect()
                ->route('erpaccount.approvals.index')
                ->with('success', 'Payroll journal posted successfully as ' . $journal->voucher_no . '.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to approve payroll: ' . $e->getMessage());
        }
    }

    private function resolveInventoryLedgers(string $type, string $description): array
    {
        $isIssue = false;
        $typeLower = strtolower($type);
        $descLower = strtolower($description);

        if (
            str_contains($typeLower, 'issue') ||
            str_contains($descLower, 'issue') ||
            str_contains($descLower, 'issued')
        ) {
            $isIssue = true;
        }

        if ($isIssue) {
            $debitLedger = $this->resolveLedger(['work in progress', 'wip', 'production in process'], ['Asset', 'Expense']);
            
            if (str_contains($typeLower, 'fabric') || str_contains($descLower, 'fabric')) {
                $creditKeywords = ['fabric stock', 'fabric inventory', 'inventory / stock'];
            } elseif (str_contains($typeLower, 'yarn') || str_contains($descLower, 'yarn')) {
                $creditKeywords = ['yarn stock', 'yarn inventory', 'inventory / stock'];
            } elseif (str_contains($typeLower, 'trim') || str_contains($descLower, 'trim')) {
                $creditKeywords = ['trims & accessories stock', 'trims stock', 'inventory / stock'];
            } else {
                $creditKeywords = ['inventory / stock', 'fabric stock', 'yarn stock', 'trims & accessories stock'];
            }

            $creditLedger = $this->resolveLedger($creditKeywords, ['Asset']);
        } else {
            if (str_contains($typeLower, 'fabric') || str_contains($descLower, 'fabric')) {
                $debitKeywords = ['fabric stock', 'fabric inventory', 'inventory / stock'];
            } elseif (str_contains($typeLower, 'yarn') || str_contains($descLower, 'yarn')) {
                $debitKeywords = ['yarn stock', 'yarn inventory', 'inventory / stock'];
            } elseif (str_contains($typeLower, 'trim') || str_contains($descLower, 'trim')) {
                $debitKeywords = ['trims & accessories stock', 'trims stock', 'inventory / stock'];
            } else {
                $debitKeywords = ['inventory / stock', 'fabric stock', 'yarn stock', 'trims & accessories stock'];
            }

            $debitLedger = $this->resolveLedger($debitKeywords, ['Asset']);
            $creditLedger = $this->resolveLedger(['supplier payable', 'accounts payable', 'trade payable', 'payable'], ['Liability']);
        }

        return [$debitLedger, $creditLedger, $isIssue];
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
}
