<?php

namespace ME\Erpaccount\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use ME\Erpaccount\Support\FinancialPeriodGuard;

class FinancialPeriodOpen implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        if (!FinancialPeriodGuard::isDateOpen($value)) {
            $period = FinancialPeriodGuard::closedPeriodForDate($value);
            $label = $period?->period_name ?? 'a closed financial period';

            $fail("The {$attribute} falls in {$label}. Posting is not allowed.");
        }
    }
}
