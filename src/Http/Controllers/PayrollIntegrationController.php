<?php

namespace ME\Erpaccount\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use ME\Erpaccount\Http\Requests\ManualPayrollVoucherRequest;
use ME\Erpaccount\Models\ChartOfAccount;
use ME\Erpaccount\Models\JournalDetail;
use ME\Erpaccount\Models\JournalMaster;
use ME\Erpaccount\Support\VoucherNumberGenerator;

class PayrollIntegrationController extends Controller
{
    public function index(Request $request)
    {
        $months = $this->recentManualMonths();
        $summary = [
            'pending_batches' => 0,
            'posted_batches' => 0,
            'total_expense' => 0,
            'net_payable' => 0,
        ];

        return view('erpaccount::phase3.payroll.index', [
            'summary' => $summary,
            'pendingBatches' => collect(),
            'postedBatches' => collect(),
            'months' => $months,
        ]);
    }

    public function store(ManualPayrollVoucherRequest $request): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $salaryExpense = $this->resolveLedger(['salary expense', 'wages expense', 'payroll expense'], ['Expense']);
            $pfLiability = $this->resolveLedger(['provident fund liability', 'pf liability', 'provident fund payable'], ['Liability']);
            $advanceAsset = $this->resolveLedger(['advance salary', 'salary advance', 'advance salary asset'], ['Asset']);
            $salaryPayable = $this->resolveLedger(['salary payable', 'wages payable', 'payable'], ['Liability']);
            // dd($salaryExpense, $pfLiability, $advanceAsset, $salaryPayable);

            if ($salaryExpense === null || $pfLiability === null || $advanceAsset === null || $salaryPayable === null) {
                DB::rollBack();
                return redirect()->back()->withErrors(['error' => 'Required payroll ledger accounts were not found.'])->withInput();
            }

            $journal = JournalMaster::query()->create([
                'voucher_no' => VoucherNumberGenerator::next('JV'),
                'journal_date' => Carbon::parse($request->input('month') . '-01')->endOfMonth()->toDateString(),
                'source_module' => 'Payroll',
                'source_reference_id' => null,
                'narration' => $request->input('narration') ?: ('Manual payroll voucher for ' . $request->input('month')),
                'created_by' => auth()->id(),
            ]);

            $grossSalary = round((float) $request->input('gross_salary'), 2);
            $allowances = round((float) $request->input('total_allowances'), 2);
            $bonus = round((float) $request->input('total_bonus'), 2);
            $advanceAdjusted = round((float) $request->input('advance_salary_adjusted'), 2);
            $pfDeduction = round((float) $request->input('provident_fund_deduction'), 2);
            $netPayable = round((float) $request->input('net_payable'), 2);
            $debitTotal = round($grossSalary + $allowances + $bonus, 2);
            $creditTotal = round($advanceAdjusted + $pfDeduction + $netPayable, 2);

            if ($debitTotal !== $creditTotal) {
                DB::rollBack();
                return redirect()->back()->withErrors(['net_payable' => 'The voucher must balance before posting.'])->withInput();
            }

            JournalDetail::query()->create([
                'journal_id' => $journal->journal_id,
                'account_id' => $salaryExpense->account_id,
                'cost_center_id' => null,
                'party_type' => 'None',
                'party_id' => null,
                'debit_amount' => $debitTotal,
                'credit_amount' => 0,
            ]);

            JournalDetail::query()->create([
                'journal_id' => $journal->journal_id,
                'account_id' => $pfLiability->account_id,
                'cost_center_id' => null,
                'party_type' => 'None',
                'party_id' => null,
                'debit_amount' => 0,
                'credit_amount' => $pfDeduction,
            ]);

            JournalDetail::query()->create([
                'journal_id' => $journal->journal_id,
                'account_id' => $advanceAsset->account_id,
                'cost_center_id' => null,
                'party_type' => 'None',
                'party_id' => null,
                'debit_amount' => 0,
                'credit_amount' => $advanceAdjusted,
            ]);

            JournalDetail::query()->create([
                'journal_id' => $journal->journal_id,
                'account_id' => $salaryPayable->account_id,
                'cost_center_id' => null,
                'party_type' => 'None',
                'party_id' => null,
                'debit_amount' => 0,
                'credit_amount' => $netPayable,
            ]);

            DB::commit();

            return redirect()
                ->route('erpaccount.manual-payroll.index')
                ->with('success', 'Manual payroll voucher posted successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function recentManualMonths(): array
    {
        return collect(range(0, 5))
            ->map(function (int $offset) {
                return now()->subMonthsNoOverflow($offset)->format('Y-m');
            })
            ->all();
    }

    private function resolveLedger(array $keywords, array $types)
    {
        $query = ChartOfAccount::query()->where('is_active', true)->whereIn('account_type', $types);

        $query->where(function ($builder) use ($keywords) {
            foreach ($keywords as $keyword) {
                $builder->orWhereRaw('LOWER(account_name) like ?', ['%' . strtolower($keyword) . '%']);
            }
        });

        return $query->orderBy('account_code')->first();
    }
}
