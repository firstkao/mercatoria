<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class BulkPriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'price_rmb' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:product_variants,id'],
        ];
    }
}
