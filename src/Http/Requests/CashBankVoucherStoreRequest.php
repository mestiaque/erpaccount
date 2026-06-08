<?php

namespace ME\Erpaccount\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use ME\Erpaccount\Rules\FinancialPeriodOpen;

class CashBankVoucherStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'voucher_type' => ['required', 'in:receipt,payment'],
            'journal_date' => ['required', 'date', new FinancialPeriodOpen()],
            'main_account_id' => ['required', 'integer', 'exists:acc_chart_of_accounts,account_id'],
            'narration' => ['nullable', 'string', 'max:500'],
            'rows' => ['required', 'array', 'min:1'],
            'rows.*.account_id' => ['required', 'integer', 'exists:acc_chart_of_accounts,account_id'],
            'rows.*.amount' => ['required', 'numeric', 'gt:0'],
            'rows.*.cost_center_id' => ['nullable', 'integer', 'exists:acc_cost_centers,cost_center_id'],
            'rows.*.party_type' => ['nullable', 'in:Buyer,Supplier,Employee,None'],
            'rows.*.party_id' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $rows = $this->input('rows', []);
            $mainAccountId = (int) $this->input('main_account_id');
            $rowsTotal = 0.0;

            foreach ($rows as $idx => $row) {
                $accountId = (int) ($row['account_id'] ?? 0);

                if ($accountId === $mainAccountId) {
                    $validator->errors()->add("rows.$idx.account_id", 'Offset account cannot be the same as selected cash/bank account.');
                }

                $rowsTotal += (float) ($row['amount'] ?? 0);
            }

            if (round($rowsTotal, 2) <= 0) {
                $validator->errors()->add('rows', 'At least one valid amount row is required.');
            }
        });
    }
}
