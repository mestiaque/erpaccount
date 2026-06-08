<?php

namespace ME\Erpaccount\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use ME\Erpaccount\Support\FinancialPeriodGuard;

class ManualPayrollVoucherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'month' => ['required', 'date_format:Y-m'],
            'gross_salary' => ['required', 'numeric', 'min:0'],
            'total_allowances' => ['required', 'numeric', 'min:0'],
            'total_bonus' => ['required', 'numeric', 'min:0'],
            'advance_salary_adjusted' => ['required', 'numeric', 'min:0'],
            'provident_fund_deduction' => ['required', 'numeric', 'min:0'],
            'net_payable' => ['required', 'numeric', 'min:0'],
            'narration' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $debit = (float) $this->input('gross_salary') + (float) $this->input('total_allowances') + (float) $this->input('total_bonus');
            $credit = (float) $this->input('advance_salary_adjusted') + (float) $this->input('provident_fund_deduction') + (float) $this->input('net_payable');

            if (round($debit, 2) !== round($credit, 2)) {
                $validator->errors()->add('net_payable', 'The voucher must balance: total debit must equal total credit.');
            }

            if ($this->filled('month')) {
                $journalDate = Carbon::parse($this->input('month') . '-01')->endOfMonth();
                if (!FinancialPeriodGuard::isDateOpen($journalDate)) {
                    $period = FinancialPeriodGuard::closedPeriodForDate($journalDate);
                    $label = $period?->period_name ?? 'a closed financial period';
                    $validator->errors()->add('month', "Payroll month falls in {$label}. Posting is not allowed.");
                }
            }
        });
    }
}
