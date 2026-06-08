<?php

namespace ME\Erpaccount\Support;

use Carbon\Carbon;
use ME\Erpaccount\Models\FinancialPeriod;

class FinancialPeriodGuard
{
    public static function assertDateOpen(Carbon|string $date): void
    {
        if (!self::isDateOpen($date)) {
            $parsed = $date instanceof Carbon ? $date : Carbon::parse($date);
            $period = self::closedPeriodForDate($parsed);

            $label = $period?->period_name ?? 'selected period';
            throw new \InvalidArgumentException(
                'Journal date ' . $parsed->toDateString() . ' falls in closed financial period: ' . $label . '.'
            );
        }
    }

    public static function isDateOpen(Carbon|string $date): bool
    {
        return self::closedPeriodForDate($date) === null;
    }

    public static function closedPeriodForDate(Carbon|string $date): ?FinancialPeriod
    {
        $parsed = $date instanceof Carbon ? $date->copy() : Carbon::parse($date);

        return FinancialPeriod::query()
            ->where('is_closed', true)
            ->whereDate('start_date', '<=', $parsed->toDateString())
            ->whereDate('end_date', '>=', $parsed->toDateString())
            ->orderByDesc('start_date')
            ->first();
    }
}
