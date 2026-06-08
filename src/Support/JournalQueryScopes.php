<?php

namespace ME\Erpaccount\Support;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\Schema;

class JournalQueryScopes
{
    protected static ?bool $hasIsVoidedColumn = null;

    public static function restrictActiveMasters(Builder $query, string $masterAlias = 'jm'): Builder
    {
        if (!self::supportsVoidColumns()) {
            return $query;
        }

        return $query->where("{$masterAlias}.is_voided", false);
    }

    public static function activeMasterOnJoin(JoinClause $join, string $detailColumn = 'jd.journal_id', string $masterAlias = 'jm'): void
    {
        $join->on("{$masterAlias}.journal_id", '=', $detailColumn);

        if (self::supportsVoidColumns()) {
            $join->where("{$masterAlias}.is_voided", '=', false);
        }
    }

    protected static function supportsVoidColumns(): bool
    {
        if (self::$hasIsVoidedColumn !== null) {
            return self::$hasIsVoidedColumn;
        }

        self::$hasIsVoidedColumn = Schema::hasTable('acc_journal_masters')
            && Schema::hasColumn('acc_journal_masters', 'is_voided');

        return self::$hasIsVoidedColumn;
    }
}
