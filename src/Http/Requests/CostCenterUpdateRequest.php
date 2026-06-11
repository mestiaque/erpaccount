<?php

namespace ME\Erpaccount\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use ME\Erpaccount\Models\CostCenter;

class CostCenterUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var CostCenter|null $costCenter */
        $costCenter = $this->route('costCenter');

        return [
            'cost_center_type' => ['required', 'string', 'max:50', 'exists:acc_cost_center_types,type_name'],
            'reference_id' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('acc_cost_centers', 'reference_id')
                    ->ignore($costCenter?->cost_center_id, 'cost_center_id')
                    ->where(fn ($query) => $query->where('cost_center_type', $this->input('cost_center_type'))),
            ],
            'cost_center_name' => ['required', 'string', 'max:150'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'cost_center_type' => trim((string) $this->input('cost_center_type', '')),
            'cost_center_name' => trim((string) $this->input('cost_center_name', '')),
        ]);
    }
}
