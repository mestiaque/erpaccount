<?php

namespace ME\Erpaccount\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use ME\Erpaccount\Support\JournalQueryScopes;

class ReportQueryHelper
{
    public function parsePeriod(array $filters): array
    {
        $start = !empty($filters['start_date'])
            ? Carbon::parse($filters['start_date'])->startOfDay()
            : now()->startOfMonth()->startOfDay();

        $end = !empty($filters['end_date'])
            ? Carbon::parse($filters['end_date'])->endOfDay()
            : now()->endOfMonth()->endOfDay();

        if ($end->lt($start)) {
            [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
        }

        $asOn = !empty($filters['as_on_date'])
            ? Carbon::parse($filters['as_on_date'])->endOfDay()
            : $end->copy();

        return [$start, $end, $asOn];
    }

    public function accountNameMatches(string $name, array $keywords): bool
    {
        $needle = strtolower($name);
        foreach ($keywords as $keyword) {
            if (str_contains($needle, strtolower($keyword))) {
                return true;
            }
        }

        return false;
    }

    public function accountIdsByKeywords(array $keywords, array $types = []): array
    {
        $query = DB::table('acc_chart_of_accounts')->where('is_active', true);

        if (!empty($types)) {
            $query->whereIn('account_type', $types);
        }

        $query->where(function ($builder) use ($keywords) {
            foreach ($keywords as $keyword) {
                $builder->orWhereRaw('LOWER(account_name) LIKE ?', ['%' . strtolower($keyword) . '%'])
                    ->orWhereRaw('LOWER(account_code) LIKE ?', ['%' . strtolower($keyword) . '%']);
            }
        });

        return $query->pluck('account_id')->map(fn ($id) => (int) $id)->all();
    }

    public function journalLines(
        Carbon $start,
        Carbon $end,
        ?array $accountIds = null,
        ?string $partyType = null,
        ?string $voucherPrefix = null,
        ?int $costCenterId = null
    ): Collection {
        $query = DB::table('acc_journal_details as jd')
            ->join('acc_journal_masters as jm', function ($join) {
                JournalQueryScopes::activeMasterOnJoin($join);
            })
            ->join('acc_chart_of_accounts as coa', 'coa.account_id', '=', 'jd.account_id')
            ->whereBetween('jm.journal_date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('jm.journal_date')
            ->orderBy('jm.journal_id')
            ->orderBy('jd.detail_id')
            ->select([
                'jm.journal_date',
                'jm.voucher_no',
                'jm.narration',
                'jm.source_module',
                'coa.account_code',
                'coa.account_name',
                'coa.account_type',
                'jd.party_type',
                'jd.party_id',
                'jd.cost_center_id',
                'jd.debit_amount',
                'jd.credit_amount',
            ]);

        if (!empty($accountIds)) {
            $query->whereIn('jd.account_id', $accountIds);
        }

        if ($partyType !== null) {
            $query->where('jd.party_type', $partyType);
        }

        if ($voucherPrefix !== null) {
            $query->where('jm.voucher_no', 'like', $voucherPrefix . '%');
        }

        if ($costCenterId !== null) {
            $query->where('jd.cost_center_id', $costCenterId);
        }

        return $query->get();
    }

    public function accountBalancesAsOn(Carbon $asOn, ?array $accountIds = null, ?array $types = null): Collection
    {
        $query = DB::table('acc_chart_of_accounts as coa')
            ->leftJoin('acc_journal_details as jd', 'jd.account_id', '=', 'coa.account_id')
            ->leftJoin('acc_journal_masters as jm', function ($join) {
                JournalQueryScopes::activeMasterOnJoin($join);
            })
            ->where('coa.is_active', true)
            ->where(function ($q) use ($asOn) {
                $q->whereNull('jm.journal_id')
                    ->orWhereDate('jm.journal_date', '<=', $asOn->toDateString());
            });

        if (!empty($accountIds)) {
            $query->whereIn('coa.account_id', $accountIds);
        }

        if (!empty($types)) {
            $query->whereIn('coa.account_type', $types);
        }

        return $query
            ->groupBy('coa.account_id', 'coa.account_code', 'coa.account_name', 'coa.account_type')
            ->orderBy('coa.account_code')
            ->select([
                'coa.account_id',
                'coa.account_code',
                'coa.account_name',
                'coa.account_type',
            ])
            ->selectRaw('COALESCE(SUM(jd.debit_amount), 0) as total_debit')
            ->selectRaw('COALESCE(SUM(jd.credit_amount), 0) as total_credit')
            ->get()
            ->map(function ($row) {
                $balance = in_array($row->account_type, ['Asset', 'Expense'], true)
                    ? (float) $row->total_debit - (float) $row->total_credit
                    : (float) $row->total_credit - (float) $row->total_debit;

                $row->balance = round($balance, 2);
                $row->side = $balance >= 0 ? 'Dr' : 'Cr';

                return $row;
            });
    }

    public function periodMovement(Carbon $start, Carbon $end, ?array $accountIds = null): Collection
    {
        $query = DB::table('acc_chart_of_accounts as coa')
            ->leftJoin('acc_journal_details as jd', 'jd.account_id', '=', 'coa.account_id')
            ->leftJoin('acc_journal_masters as jm', function ($join) {
                JournalQueryScopes::activeMasterOnJoin($join);
            })
            ->where('coa.is_active', true);

        if (!empty($accountIds)) {
            $query->whereIn('coa.account_id', $accountIds);
        }

        return $query
            ->groupBy('coa.account_id', 'coa.account_code', 'coa.account_name', 'coa.account_type')
            ->orderBy('coa.account_code')
            ->select([
                'coa.account_id',
                'coa.account_code',
                'coa.account_name',
                'coa.account_type',
            ])
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN jm.journal_date BETWEEN ? AND ? THEN jd.debit_amount ELSE 0 END), 0) as period_debit',
                [$start->toDateString(), $end->toDateString()]
            )
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN jm.journal_date BETWEEN ? AND ? THEN jd.credit_amount ELSE 0 END), 0) as period_credit',
                [$start->toDateString(), $end->toDateString()]
            )
            ->get();
    }

    public function accountBalancesWithGroupAsOn(Carbon $asOn): Collection
    {
        return DB::table('acc_chart_of_accounts as coa')
            ->leftJoin('acc_chart_of_accounts as grp', 'grp.account_id', '=', 'coa.parent_id')
            ->leftJoin('acc_journal_details as jd', 'jd.account_id', '=', 'coa.account_id')
            ->leftJoin('acc_journal_masters as jm', function ($join) {
                JournalQueryScopes::activeMasterOnJoin($join);
            })
            ->where('coa.is_active', true)
            ->where(function ($q) use ($asOn) {
                $q->whereNull('jm.journal_id')
                    ->orWhereDate('jm.journal_date', '<=', $asOn->toDateString());
            })
            ->groupBy(
                'coa.account_id', 'coa.account_code', 'coa.account_name', 'coa.account_type',
                'coa.parent_id', 'grp.account_name', 'grp.account_code'
            )
            ->orderBy('coa.account_type')
            ->orderBy('grp.account_code')
            ->orderBy('coa.account_code')
            ->select([
                'coa.account_id',
                'coa.account_code',
                'coa.account_name',
                'coa.account_type',
                'coa.parent_id',
                DB::raw('COALESCE(grp.account_name, coa.account_name) as group_name'),
                DB::raw('COALESCE(grp.account_code, coa.account_code) as group_code'),
            ])
            ->selectRaw('COALESCE(SUM(jd.debit_amount), 0) as total_debit')
            ->selectRaw('COALESCE(SUM(jd.credit_amount), 0) as total_credit')
            ->get()
            ->map(function ($row) {
                $balance = in_array($row->account_type, ['Asset', 'Expense'], true)
                    ? (float) $row->total_debit - (float) $row->total_credit
                    : (float) $row->total_credit - (float) $row->total_debit;
                $row->balance = round($balance, 2);
                return $row;
            });
    }
}
