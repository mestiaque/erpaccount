<?php

namespace ME\Erpaccount\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use ME\Erpaccount\Models\JournalMaster;
use ME\Erpaccount\Support\FinancialPeriodGuard;

class VoucherRegisterController extends Controller
{
    public function index(Request $request)
    {
        $query = JournalMaster::query()
            ->withSum('details as total_debit', 'debit_amount')
            ->withSum('details as total_credit', 'credit_amount')
            ->orderByDesc('journal_date')
            ->orderByDesc('journal_id');

        if ($request->filled('voucher_no')) {
            $query->where('voucher_no', 'like', '%' . trim($request->input('voucher_no')) . '%');
        }

        if ($request->filled('source_module')) {
            $query->where('source_module', $request->input('source_module'));
        }

        if ($request->filled('status') && JournalMaster::supportsVoidColumns()) {
            if ($request->input('status') === 'voided') {
                $query->where('is_voided', true);
            } elseif ($request->input('status') === 'active') {
                $query->where('is_voided', false);
            }
        }

        if ($request->filled('start_date')) {
            $query->whereDate('journal_date', '>=', $request->input('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('journal_date', '<=', $request->input('end_date'));
        }

        $vouchers = $query->paginate(25)->withQueryString();

        if ($this->isApiRequest($request)) {
            return response()->json(['data' => $vouchers]);
        }

        return view('erpaccount::phase2.voucher_register.index', [
            'vouchers' => $vouchers,
            'filters' => $request->only(['voucher_no', 'source_module', 'status', 'start_date', 'end_date']),
            'sourceModules' => ['Manual', 'Inventory', 'Production', 'Commercial', 'Payroll'],
        ]);
    }

    public function show(Request $request, JournalMaster $journalMaster)
    {
        $journalMaster->load(['details.chartOfAccount']);

        if ($this->isApiRequest($request)) {
            return response()->json(['data' => $journalMaster]);
        }

        return view('erpaccount::phase2.voucher_register.show', [
            'voucher' => $journalMaster,
            'totalDebit' => round((float) $journalMaster->details->sum('debit_amount'), 2),
            'totalCredit' => round((float) $journalMaster->details->sum('credit_amount'), 2),
        ]);
    }

    public function void(Request $request, JournalMaster $journalMaster): JsonResponse|RedirectResponse
    {
        if (!JournalMaster::supportsVoidColumns()) {
            return $this->respond($request, 'Void feature is unavailable until void columns migration is applied.', 422, 'warning');
        }

        if ($journalMaster->is_voided) {
            return $this->respond($request, 'This voucher is already voided.', 422, 'warning');
        }

        try {
            FinancialPeriodGuard::assertDateOpen($journalMaster->journal_date);
        } catch (\InvalidArgumentException $e) {
            return $this->respond($request, $e->getMessage(), 422);
        }

        $validated = $request->validate([
            'void_reason' => ['required', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($journalMaster, $validated) {
            $journalMaster->update([
                'is_voided' => true,
                'voided_at' => now(),
                'voided_by' => auth()->id(),
                'void_reason' => $validated['void_reason'],
            ]);
        });

        return $this->respond(
            $request,
            'Voucher ' . $journalMaster->voucher_no . ' voided successfully.',
            200,
            'success',
            route('erpaccount.voucher-register.show', $journalMaster)
        );
    }

    private function respond(
        Request $request,
        string $message,
        int $status = 200,
        string $flashType = 'success',
        ?string $redirectRoute = null
    ): JsonResponse|RedirectResponse {
        if ($this->isApiRequest($request)) {
            return response()->json(['message' => $message], $status);
        }

        $redirect = $redirectRoute
            ? redirect()->to($redirectRoute)
            : redirect()->back();

        return $redirect->with($flashType, $message);
    }

    private function isApiRequest(Request $request): bool
    {
        return $request->expectsJson() || str_starts_with($request->path(), 'api/');
    }
}
