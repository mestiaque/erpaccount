<?php

namespace ME\Erpaccount\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BankStatementUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bank_account_id' => ['required', 'integer', 'exists:acc_bank_accounts,bank_account_id'],
            'entries' => ['required', 'array', 'min:1'],
            'entries.*.statement_date' => ['required', 'date'],
            'entries.*.reference_no' => ['nullable', 'string', 'max:100'],
            'entries.*.description' => ['nullable', 'string', 'max:255'],
            'entries.*.debit_amount' => ['nullable', 'numeric', 'min:0'],
            'entries.*.credit_amount' => ['nullable', 'numeric', 'min:0'],
            'entries.*.closing_balance' => ['nullable', 'numeric'],
        ];
    }
}
