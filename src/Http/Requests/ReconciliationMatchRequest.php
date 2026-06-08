<?php

namespace ME\Erpaccount\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReconciliationMatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'detail_id' => ['required', 'integer', 'exists:acc_journal_details,detail_id'],
            'statement_id' => ['required', 'integer', 'exists:acc_bank_statement_entries,statement_id'],
            'is_matched' => ['required', 'boolean'],
        ];
    }
}
