<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class MarketplaceSettingsRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'marketplaces' => ['required', 'array'],
            'marketplaces.*.fp_fee_idr' => ['required', 'integer', 'min:0', 'max:10000000'],
            'marketplaces.*.dp_fee_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'marketplaces.*.is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'marketplaces.*.fp_fee_idr' => 'biaya FP',
            'marketplaces.*.dp_fee_percent' => 'biaya DP',
        ];
    }
}