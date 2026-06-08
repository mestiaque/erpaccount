<?php

namespace ME\Erpaccount\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use ME\Erpaccount\Rules\FinancialPeriodOpen;

class ManualInventoryJournalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'entry_date' => ['required', 'date', new FinancialPeriodOpen()],
            'cost_center_id' => ['nullable', 'integer', 'exists:acc_cost_centers,cost_center_id'],
            'transaction_type' => ['required', 'in:Material Purchase,Issue to Production (WIP),Inventory Adjustment/Loss'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'currency' => ['required', 'in:BDT,USD'],
            'remarks' => ['required', 'string', 'max:255'],
        ];
    }
}
