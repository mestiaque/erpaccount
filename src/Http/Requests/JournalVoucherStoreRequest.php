<?php

namespace ME\Erpaccount\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use ME\Erpaccount\Rules\FinancialPeriodOpen;

class JournalVoucherStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'journal_date' => ['required', 'date', new FinancialPeriodOpen()],
            'narration' => ['nullable', 'string', 'max:500'],
            'rows' => ['required', 'array', 'min:2'],
            'rows.*.account_id' => ['required', 'integer', 'exists:acc_chart_of_accounts,account_id'],
            'rows.*.cost_center_id' => ['nullable', 'integer', 'exists:acc_cost_centers,cost_center_id'],
            'rows.*.party_type' => ['nullable', 'in:Buyer,Supplier,Employee,None'],
            'rows.*.party_id' => ['nullable', 'integer', 'min:1'],
            'rows.*.debit' => ['required', 'numeric', 'min:0'],
            'rows.*.credit' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $rows = $this->input('rows', []);

            $debitTotal = 0.0;
            $creditTotal = 0.0;

            foreach ($rows as $idx => $row) {
                $debit = (float) ($row['debit'] ?? 0);
                $credit = (float) ($row['credit'] ?? 0);

                if ($debit > 0 && $credit > 0) {
                    $validator->errors()->add("rows.$idx.debit", 'A row cannot contain both debit and credit amounts.');
                }

                if ($debit <= 0 && $credit <= 0) {
                    $validator->errors()->add("rows.$idx.debit", 'Each row must have either a debit or a credit amount.');
                }

                $debitTotal += $debit;
                $creditTotal += $credit;
            }

            if (round($debitTotal, 2) !== round($creditTotal, 2)) {
                $validator->errors()->add('rows', 'Total debit must exactly match total credit.');
            }
        });
    }
}
