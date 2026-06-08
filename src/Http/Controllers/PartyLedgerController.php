<?php

namespace ME\Erpaccount\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ME\Erpaccount\Models\ChartOfAccount;
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
        $userModelClass = config('auth.providers.users.model');
        if (!is_string($userModelClass) || !class_exists($userModelClass)) {
            return [
                'name' => $partyType . '-' . $partyId,
                'ledger' => null,
            ];
        }

        $model = app($userModelClass);
        if (!$model instanceof Model) {
            return [
                'name' => $partyType . '-' . $partyId,
                'ledger' => null,
            ];
        }

        $query = $userModelClass::query()->whereKey($partyId);

        if (method_exists($model, 'scopeFilterByType')) {
            $query->filterByType(strtolower($partyType));
        } else {
            $table = $model->getTable();
            if (Schema::hasColumn($table, 'type')) {
                $query->where('type', strtolower($partyType));
            } elseif (Schema::hasColumn($table, 'user_type')) {
                $query->where('user_type', strtolower($partyType));
            }
        }

        $user = $query->first();
        if (!$user instanceof Model) {
            return [
                'name' => $partyType . '-' . $partyId,
                'ledger' => null,
            ];
        }

        return [
            'name' => $this->resolvePartyDisplayName($user),
            'ledger' => $this->resolvePartyLedgerLabel($user, $partyType),
        ];
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
