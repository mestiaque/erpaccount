<?php

namespace ME\Erpaccount\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PayrollBridgeReceiveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payroll_month' => ['required', 'date_format:Y-m'],
            'staff_basic' => ['required', 'numeric', 'min:0'],
            'staff_allowances' => ['required', 'numeric', 'min:0'],
            'staff_pf_deductions' => ['required', 'numeric', 'min:0'],
            'factory_piece_rate_earnings' => ['required', 'numeric', 'min:0'],
            'factory_overtime_amount' => ['required', 'numeric', 'min:0'],
            'payload' => ['nullable', 'array'],
        ];
    }
}
