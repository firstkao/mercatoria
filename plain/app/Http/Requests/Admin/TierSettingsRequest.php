<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;

class TierSettingsRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'tiers' => collect((array) $this->input('tiers', []))
                ->map(fn ($tier) => [...(array) $tier, 'code' => Str::upper(trim((string) ($tier['code'] ?? '')))])
                ->all(),
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'tiers' => ['required', 'array', 'min:1'],
            'tiers.*.id' => ['nullable', 'integer', 'exists:shipping_tiers,id'],
            'tiers.*.code' => ['required', 'string', 'max:10', 'distinct'],
            'tiers.*.fee_yuan' => ['required', 'numeric', 'min:0', 'max:100000'],
            'tiers.*.min_purchase_yuan' => ['required', 'numeric', 'min:0', 'max:1000000'],
            'tiers.*.delete' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                foreach ((array) $this->input('tiers', []) as $index => $tier) {
                    if ((float) ($tier['fee_yuan'] ?? 0) > 0 && (float) ($tier['min_purchase_yuan'] ?? 0) <= 0) {
                        $validator->errors()->add("tiers.{$index}.min_purchase_yuan", 'Tier berbayar harus punya minimal belanja lebih dari ¥0.');
                    }
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return ['tiers.*.code.distinct' => 'Kode tier tidak boleh sama.'];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'tiers.*.code' => 'kode tier',
            'tiers.*.fee_yuan' => 'fee tier',
            'tiers.*.min_purchase_yuan' => 'minimal belanja',
        ];
    }
}