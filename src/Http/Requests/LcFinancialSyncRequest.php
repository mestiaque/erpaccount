<?php

namespace ME\Erpaccount\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LcFinancialSyncRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'lc_type' => ['required', 'in:Master_LC,Back_To_Back_LC'],
            'lc_id_reference' => ['required', 'integer'],
            'total_lc_value' => ['required', 'numeric', 'gt:0'],
            'currency' => ['required', 'string', 'max:10'],
            'exchange_rate' => ['required', 'numeric', 'gt:0'],
            'bank_margin_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'bank_margin_limit' => ['nullable', 'numeric', 'min:0'],
            'bank_margin_used' => ['nullable', 'numeric', 'min:0'],
            'bank_commission_paid' => ['nullable', 'numeric', 'min:0'],
            'acceptance_cost_paid' => ['nullable', 'numeric', 'min:0'],
            'outstanding_liability' => ['nullable', 'numeric', 'min:0'],
            'customs_clearing_cost' => ['nullable', 'numeric', 'min:0'],
            'freight_cost' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
