<?php

namespace ME\Erpaccount\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use ME\Erpaccount\Models\ChartOfAccount;
use ME\Erpaccount\Models\IouMaster;
use ME\Erpaccount\Models\IouSettlement;
use ME\Erpaccount\Models\JournalDetail;
use ME\Erpaccount\Models\JournalMaster;
use ME\Erpaccount\Support\VoucherNumberGenerator;

class IouController extends Controller
{
    // ──────────────────────────────────────────────
    // List + Issue form
    // ──────────────────────────────────────────────
    public function index(Request $request)
    {
        $query = IouMaster::query()
            ->with(['iouAccount', 'cashAccount'])
            ->orderByDesc('issue_date')
            ->orderByDesc('iou_id');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('party_type')) {
            $query->where('party_type', $request->input('party_type'));
        }
        if ($request->filled('search')) {
            $search = '%' . trim((string) $request->input('search')) . '%';
            $query->where(function ($b) use ($search) {
                $b->where('party_name', 'like', $search)
                    ->orWhere('iou_no', 'like', $search)
                    ->orWhere('purpose', 'like', $search);
            });
        }

        $ious = $query->paginate(20)->withQueryString();

        $accounts = ChartOfAccount::query()
            ->where('is_active', true)
            ->orderBy('account_code')
            ->get();

        $employees = $this->loadEmployees();

        return view('erpaccount::phase2.iou.index', [
            'ious'      => $ious,
            'accounts'  => $accounts,
            'employees' => $employees,
            'filters'   => $request->only(['status', 'party_type', 'search']),
        ]);
    }

    // ──────────────────────────────────────────────
    // Issue new IOU
    // ──────────────────────────────────────────────
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'party_type'     => ['required', 'in:employee,custom'],
            'party_id'       => ['nullable', 'integer'],
            'party_name'     => ['required_if:party_type,custom', 'nullable', 'string', 'max:150'],
            'original_amount'=> ['required', 'numeric', 'min:0.01'],
            'issue_date'     => ['required', 'date'],
            'purpose'        => ['nullable', 'string', 'max:255'],
            'iou_account_id' => ['required', 'integer', 'exists:acc_chart_of_accounts,account_id'],
            'cash_account_id'=> ['required', 'integer', 'exists:acc_chart_of_accounts,account_id'],
        ]);

        // Resolve party name for employee
        if ($validated['party_type'] === 'employee') {
            $empName = $this->resolveEmployeeName((int) ($validated['party_id'] ?? 0));
            $validated['party_name'] = $empName;
        }

        DB::beginTransaction();
        try {
            $iouNo = $this->nextIouNumber();

            // Journal entry: Dr IOU Receivable / Cr Cash-Bank
            $voucherNo = VoucherNumberGenerator::next('IOU');
            $narration = 'IOU issued to ' . $validated['party_name']
                . ($validated['purpose'] ? ' — ' . $validated['purpose'] : '');

            $journal = JournalMaster::query()->create([
                'voucher_no'          => $voucherNo,
                'journal_date'        => $validated['issue_date'],
                'source_module'       => 'IOU',
                'source_reference_id' => null,
                'narration'           => $narration,
                'created_by'          => auth()->id(),
            ]);

            JournalDetail::query()->create([
                'journal_id'    => $journal->journal_id,
                'account_id'    => (int) $validated['iou_account_id'],
                'cost_center_id'=> null,
                'party_type'    => 'None',
                'party_id'      => null,
                'debit_amount'  => round((float) $validated['original_amount'], 2),
                'credit_amount' => 0.00,
            ]);

            JournalDetail::query()->create([
                'journal_id'    => $journal->journal_id,
                'account_id'    => (int) $validated['cash_account_id'],
                'cost_center_id'=> null,
                'party_type'    => 'None',
                'party_id'      => null,
                'debit_amount'  => 0.00,
                'credit_amount' => round((float) $validated['original_amount'], 2),
            ]);

            IouMaster::query()->create([
                'iou_no'          => $iouNo,
                'party_type'      => $validated['party_type'],
                'party_id'        => $validated['party_type'] === 'employee' ? ((int) ($validated['party_id'] ?? 0) ?: null) : null,
                'party_name'      => $validated['party_name'],
                'original_amount' => round((float) $validated['original_amount'], 2),
                'issue_date'      => $validated['issue_date'],
                'purpose'         => $validated['purpose'] ?? null,
                'iou_account_id'  => (int) $validated['iou_account_id'],
                'cash_account_id' => (int) $validated['cash_account_id'],
                'settled_amount'  => 0.00,
                'status'          => 'open',
                'approval_status' => 'approved',
                'journal_id'      => $journal->journal_id,
                'created_by'      => auth()->id(),
            ]);

            DB::commit();

            return redirect()
                ->route('erpaccount.iou.index')
                ->with('success', 'IOU ' . $iouNo . ' issued successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // ──────────────────────────────────────────────
    // Detail + settlement history
    // ──────────────────────────────────────────────
    public function show(IouMaster $iou): \Illuminate\Contracts\View\View
    {
        $iou->load(['iouAccount', 'cashAccount', 'settlements.offsetAccount', 'journal']);

        $accounts = ChartOfAccount::query()
            ->where('is_active', true)
            ->orderBy('account_code')
            ->get();

        return view('erpaccount::phase2.iou.show', [
            'iou'      => $iou,
            'accounts' => $accounts,
        ]);
    }

    // ──────────────────────────────────────────────
    // Add settlement
    // ──────────────────────────────────────────────
    public function settle(Request $request, IouMaster $iou): RedirectResponse
    {
        if ($iou->status === 'closed') {
            return redirect()
                ->route('erpaccount.iou.show', $iou->iou_id)
                ->with('warning', 'This IOU is already closed.');
        }

        $outstanding = round($iou->original_amount - $iou->settled_amount, 2);

        $validated = $request->validate([
            'settlement_date'   => ['required', 'date'],
            'settlement_type'   => ['required', 'in:cash,bank,salary_adjust,other'],
            'offset_account_id' => ['required', 'integer', 'exists:acc_chart_of_accounts,account_id'],
            'note'              => ['nullable', 'string', 'max:255'],
        ]);

        // Settlement always closes the full outstanding IOU amount.
        // Actual expense is recorded separately via Universal Voucher Entry.
        $settleAmount = $outstanding;

        DB::beginTransaction();
        try {
            // Journal: Dr offset account / Cr IOU Receivable
            $voucherNo = VoucherNumberGenerator::next('IOU');
            $narration = 'IOU settlement (' . $validated['settlement_type'] . ') — '
                . $iou->iou_no . ' / ' . $iou->party_name
                . ($validated['note'] ? ' — ' . $validated['note'] : '');

            $journal = JournalMaster::query()->create([
                'voucher_no'          => $voucherNo,
                'journal_date'        => $validated['settlement_date'],
                'source_module'       => 'IOU',
                'source_reference_id' => $iou->iou_id,
                'narration'           => $narration,
                'created_by'          => auth()->id(),
            ]);

            JournalDetail::query()->create([
                'journal_id'    => $journal->journal_id,
                'account_id'    => (int) $validated['offset_account_id'],
                'cost_center_id'=> null,
                'party_type'    => 'None',
                'party_id'      => null,
                'debit_amount'  => $settleAmount,
                'credit_amount' => 0.00,
            ]);

            JournalDetail::query()->create([
                'journal_id'    => $journal->journal_id,
                'account_id'    => $iou->iou_account_id,
                'cost_center_id'=> null,
                'party_type'    => 'None',
                'party_id'      => null,
                'debit_amount'  => 0.00,
                'credit_amount' => $settleAmount,
            ]);

            IouSettlement::query()->create([
                'iou_id'            => $iou->iou_id,
                'settlement_date'   => $validated['settlement_date'],
                'settled_amount'    => $settleAmount,
                'settlement_type'   => $validated['settlement_type'],
                'offset_account_id' => (int) $validated['offset_account_id'],
                'note'              => $validated['note'] ?? null,
                'approval_status'   => 'approved',
                'journal_id'        => $journal->journal_id,
                'created_by'        => auth()->id(),
            ]);

            $iou->recalculateStatus();

            DB::commit();

            return redirect()
                ->route('erpaccount.iou.show', $iou->iou_id)
                ->with('success', 'Settlement of ' . number_format((float) $validated['settled_amount'], 2) . ' recorded.');
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // ──────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────
    private function nextIouNumber(): string
    {
        $last = IouMaster::query()
            ->where('iou_no', 'like', 'IOU-%')
            ->lockForUpdate()
            ->orderByDesc('iou_id')
            ->value('iou_no');

        $next = 1;
        if ($last) {
            $parts = explode('-', $last);
            $next  = ((int) end($parts)) + 1;
        }

        return 'IOU-' . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    private function loadEmployees(): array
    {
        $class = 'ME\\Hr\\Models\\HrEmployee';
        if (!class_exists($class)) {
            return [];
        }

        return $class::query()
            ->orderBy('employee_id')
            ->limit(500)
            ->get()
            ->map(fn ($e) => [
                'id'           => $e->id,
                'display_name' => implode(' | ', array_filter([
                    $this->empStr($e->employee_id ?? null),
                    $this->empStr($e->name ?? $e->full_name ?? null),
                    $this->empStr($e->designation ?? null),
                    $this->empStr($e->department ?? null),
                ])),
            ])
            ->values()
            ->all();
    }

    private function resolveEmployeeName(int $id): string
    {
        $class = 'ME\\Hr\\Models\\HrEmployee';
        if (!class_exists($class) || $id <= 0) {
            return 'Employee-' . $id;
        }
        $emp = $class::query()->find($id);
        if (!$emp) {
            return 'Employee-' . $id;
        }
        $name = $this->empStr($emp->name ?? $emp->full_name ?? null) ?? ('Employee-' . $id);
        $empId = $this->empStr($emp->employee_id ?? null);
        return $empId ? $empId . ' — ' . $name : $name;
    }

    private function empStr(mixed $value): ?string
    {
        if ($value === null || $value === '' || is_bool($value)) return null;
        if (is_string($value)) {
            $v = trim($value);
            if ($v === '') return null;
            if ($v[0] === '{' || $v[0] === '[') {
                $decoded = json_decode($v, true);
                if (is_array($decoded)) {
                    return $this->empStr($decoded['name'] ?? $decoded['title'] ?? null);
                }
            }
            return $v;
        }
        if (is_numeric($value)) return (string) $value;
        if (is_array($value))  return $this->empStr($value['name'] ?? $value['title'] ?? null);
        if (is_object($value)) return $this->empStr($value->name ?? $value->title ?? null);
        return null;
    }
}
