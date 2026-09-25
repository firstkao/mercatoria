<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PricingSettingsRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'exchange_rate' => ['nullable', 'numeric', 'min:1', 'max:100000'],
            'cn_id_rate_per_kg' => ['nullable', 'numeric', 'min:0', 'max:100000000'],
            'btm_jkt_rate_per_kg' => ['nullable', 'numeric', 'min:0', 'max:100000000'],
            'margin_percent' => ['required', 'numeric', 'min:0', 'max:500'],
            'price_rounding' => ['required', 'integer', 'min:1', 'max:1000000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'exchange_rate' => 'kurs',
            'cn_id_rate_per_kg' => 'ongkir CN–ID',
            'btm_jkt_rate_per_kg' => 'ongkir Batam–Jakarta',
            'margin_percent' => 'margin',
            'price_rounding' => 'pembulatan',
        ];
    }
}