<?php

namespace ME\Erpaccount\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use ME\Erpaccount\Models\ChartOfAccount;
use ME\Erpaccount\Models\Creditor;
use ME\Erpaccount\Models\Debitor;
use ME\Erpaccount\Support\JournalQueryScopes;

class PartyLedgerController extends Controller
{
    public function index(Request $request)
    {
        [$startDate, $endDate] = $this->resolveDateRange($request);
        $partyType = $this->normalizePartyType((string) $request->input('party_type', ''));

        $filters = [
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'party_type' => $partyType,
            'party_id' => $request->filled('party_id') ? (int) $request->input('party_id') : null,
            'account_id' => $request->filled('account_id') ? (int) $request->input('account_id') : null,
        ];

        $partyLedgerAccounts = ChartOfAccount::query()
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereRaw('LOWER(account_name) like ?', ['%receivable%'])
                    ->orWhereRaw('LOWER(account_name) like ?', ['%payable%']);
            })
            ->orderBy('account_code')
            ->get();

        $report = null;

        if ($filters['party_type'] !== '' && !empty($filters['party_id'])) {
            $report = $this->buildLedgerReport(
                $startDate,
                $endDate,
                $filters['party_type'],
                (int) $filters['party_id'],
                $filters['account_id']
            );
        }

        if ($request->expectsJson() || str_starts_with($request->path(), 'api/')) {
            return response()->json([
                'data' => [
                    'filters' => $filters,
                    'report' => $report,
                ],
            ]);
        }

        return view('erpaccount::phase4.party_ledger.index', [
            'filters' => $filters,
            'partyLedgerAccounts' => $partyLedgerAccounts,
            'report' => $report,
        ]);
    }

    public function exportExcel(Request $request)
    {
        [$startDate, $endDate] = $this->resolveDateRange($request);
        $partyType = $this->normalizePartyType((string) $request->input('party_type', ''));
        $partyId = (int) $request->input('party_id', 0);
        $accountId = $request->filled('account_id') ? (int) $request->input('account_id') : null;

        if ($partyType === '' || $partyId <= 0) {
            return redirect()
                ->route('erpaccount.party-ledger.index', [
                    'start_date' => $startDate->toDateString(),
                    'end_date' => $endDate->toDateString(),
                ])
                ->withErrors(['party' => 'Select Party Type and Party Ledger first.']);
        }

        $report = $this->buildLedgerReport($startDate, $endDate, $partyType, $partyId, $accountId);

        $fileName = 'party-ledger-' . strtolower($partyType) . '-' . $partyId . '-' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($report) {
            $out = fopen('php://output', 'w');

            fputcsv($out, ['Party Type', 'Party Name', 'Party ID', 'Ledger']);
            fputcsv($out, [
                $report['party_type'],
                $report['party_name'],
                (string) $report['party_id'],
                $report['party_ledger'] ?? 'Not Linked',
            ]);
            fputcsv($out, []);
            fputcsv($out, ['Opening Balance', number_format((float) $report['opening_balance'], 2, '.', ''), $report['opening_side']]);
            fputcsv($out, []);
            fputcsv($out, ['Date', 'Voucher No', 'Account', 'Narration', 'Debit', 'Credit', 'Running Balance']);

            foreach ($report['rows'] as $row) {
                fputcsv($out, [
                    $row->journal_date,
                    $row->voucher_no,
                    $row->account_label,
                    $row->narration,
                    number_format((float) $row->debit_amount, 2, '.', ''),
                    number_format((float) $row->credit_amount, 2, '.', ''),
                    $row->running_balance_label,
                ]);
            }

            fputcsv($out, []);
            fputcsv($out, ['Period Debit', number_format((float) $report['period_debit'], 2, '.', '')]);
            fputcsv($out, ['Period Credit', number_format((float) $report['period_credit'], 2, '.', '')]);
            fputcsv($out, ['Closing Balance', number_format((float) $report['closing_balance'], 2, '.', ''), $report['closing_side']]);

            fclose($out);
        }, $fileName, ['Content-Type' => 'text/csv']);
    }

    public function printFriendly(Request $request)
    {
        [$startDate, $endDate] = $this->resolveDateRange($request);
        $partyType = $this->normalizePartyType((string) $request->input('party_type', ''));
        $partyId = (int) $request->input('party_id', 0);
        $accountId = $request->filled('account_id') ? (int) $request->input('account_id') : null;

        if ($partyType === '' || $partyId <= 0) {
            return redirect()
                ->route('erpaccount.party-ledger.index', [
                    'start_date' => $startDate->toDateString(),
                    'end_date' => $endDate->toDateString(),
                ])
                ->withErrors(['party' => 'Select Party Type and Party Ledger first.']);
        }

        $report = $this->buildLedgerReport($startDate, $endDate, $partyType, $partyId, $accountId);

        return view('erpaccount::phase4.party_ledger.print', [
            'report' => $report,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    private function buildLedgerReport(
        Carbon $startDate,
        Carbon $endDate,
        string $partyType,
        int $partyId,
        ?int $accountId
    ): array {
        $normalizedPartyType = ucfirst(strtolower($partyType));

        $baseQuery = DB::table('acc_journal_details as jd')
            ->join('acc_journal_masters as jm', function ($join) {
                JournalQueryScopes::activeMasterOnJoin($join);
            })
            ->join('acc_chart_of_accounts as coa', 'coa.account_id', '=', 'jd.account_id')
            ->where('jd.party_type', $normalizedPartyType)
            ->where('jd.party_id', $partyId);

        if (!empty($accountId)) {
            $baseQuery->where('jd.account_id', $accountId);
        }

        $openingDebit = (float) (clone $baseQuery)
            ->whereDate('jm.journal_date', '<', $startDate->toDateString())
            ->sum('jd.debit_amount');

        $openingCredit = (float) (clone $baseQuery)
            ->whereDate('jm.journal_date', '<', $startDate->toDateString())
            ->sum('jd.credit_amount');

        $openingNet = $openingDebit - $openingCredit;

        $rows = (clone $baseQuery)
            ->whereBetween('jm.journal_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderBy('jm.journal_date')
            ->orderBy('jm.journal_id')
            ->orderBy('jd.detail_id')
            ->select([
                'jm.journal_date',
                'jm.voucher_no',
                'jm.narration',
                'coa.account_code',
                'coa.account_name',
                'jd.debit_amount',
                'jd.credit_amount',
            ])
            ->get()
            ->map(function ($row) {
                $row->account_label = $row->account_code . ' - ' . $row->account_name;
                return $row;
            });

        $running = $openingNet;

        $rows = $rows->map(function ($row) use (&$running) {
            $running += (float) $row->debit_amount;
            $running -= (float) $row->credit_amount;
            $row->running_balance = round($running, 2);
            $row->running_balance_label = $this->formatBalanceLabel($running);

            return $row;
        });

        $periodDebit = round((float) $rows->sum('debit_amount'), 2);
        $periodCredit = round((float) $rows->sum('credit_amount'), 2);
        $closingNet = $openingNet + $periodDebit - $periodCredit;

        $partyInfo = $this->resolvePartyInfo($normalizedPartyType, $partyId);

        return [
            'party_type' => $normalizedPartyType,
            'party_id' => $partyId,
            'party_name' => $partyInfo['name'],
            'party_ledger' => $partyInfo['ledger'],
            'opening_balance' => round(abs($openingNet), 2),
            'opening_side' => $this->balanceSide($openingNet),
            'period_debit' => $periodDebit,
            'period_credit' => $periodCredit,
            'closing_balance' => round(abs($closingNet), 2),
            'closing_side' => $this->balanceSide($closingNet),
            'rows' => $rows,
        ];
    }

    private function resolveDateRange(Request $request): array
    {
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->input('start_date'))->startOfDay()
            : now()->startOfMonth()->startOfDay();

        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->input('end_date'))->endOfDay()
            : now()->endOfMonth()->endOfDay();

        if ($endDate->lt($startDate)) {
            [$startDate, $endDate] = [$endDate->copy()->startOfDay(), $startDate->copy()->endOfDay()];
        }

        return [$startDate, $endDate];
    }

    private function normalizePartyType(string $partyType): string
    {
        $normalized = ucfirst(strtolower(trim($partyType)));

        if (!in_array($normalized, ['Buyer', 'Supplier', 'Employee'], true)) {
            return '';
        }

        return $normalized;
    }

    private function resolvePartyInfo(string $partyType, int $partyId): array
    {
        if ($partyType === 'Supplier') {
            $creditor = Creditor::query()->find($partyId);
            return [
                'name'   => $creditor ? $creditor->name : 'Creditor-' . $partyId,
                'ledger' => null,
            ];
        }

        if ($partyType === 'Buyer') {
            $debitor = Debitor::query()->find($partyId);
            return [
                'name'   => $debitor ? $debitor->name : 'Debitor-' . $partyId,
                'ledger' => null,
            ];
        }

        if ($partyType === 'Employee') {
            return $this->resolveEmployeeInfo($partyId);
        }

        return [
            'name'   => $partyType . '-' . $partyId,
            'ledger' => null,
        ];
    }

    private function resolveEmployeeInfo(int $partyId): array
    {
        $class = 'ME\\Hr\\Models\\HrEmployee';
        if (!class_exists($class)) {
            return ['name' => 'Employee-' . $partyId, 'ledger' => null];
        }

        $emp = $class::query()->find($partyId);
        if (!$emp) {
            return ['name' => 'Employee-' . $partyId, 'ledger' => null];
        }

        $name = $this->empStr($emp->name ?? $emp->full_name ?? null)
                ?? ('Employee-' . $partyId);

        $meta = array_values(array_filter([
            $this->empStr($emp->employee_id ?? null),
            $this->empStr($emp->designation ?? null),
            $this->empStr($emp->department  ?? null),
            $this->empStr($emp->section     ?? null),
            $this->empStr($emp->subsection  ?? null),
        ]));

        $label = $name . (!empty($meta) ? ' (' . implode(', ', $meta) . ')' : '');

        return ['name' => $label, 'ledger' => null];
    }

    private function empStr(mixed $value): ?string
    {
        if ($value === null || $value === '' || is_bool($value)) {
            return null;
        }
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
        if (is_array($value))   return $this->empStr($value['name'] ?? $value['title'] ?? null);
        if (is_object($value))  return $this->empStr($value->name ?? $value->title ?? null);
        return null;
    }

    private function balanceSide(float $value): string
    {
        if (abs($value) < 0.0001) {
            return 'Zero';
        }

        return $value >= 0 ? 'Dr' : 'Cr';
    }

    private function formatBalanceLabel(float $value): string
    {
        if (abs($value) < 0.0001) {
            return '0.00';
        }

        $side = $value >= 0 ? 'Dr' : 'Cr';

        return number_format(abs($value), 2) . ' ' . $side;
    }
}
