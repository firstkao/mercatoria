<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return match ($this->route()->getActionMethod()) {
            'bulk' => [
                'stores' => ['required', 'array', 'min:1'],
                'stores.*.id' => ['nullable', 'integer', 'exists:stores,id'],
                'stores.*.name' => ['required', 'string', 'max:100'],
                'stores.*.min_belanja_rmb' => ['required', 'numeric', 'min:0'],
                'stores.*.ongkir_rmb' => ['required', 'numeric', 'min:0'],
            ],
            'bulkDelete' => [
                'ids' => ['required', 'array', 'min:1'],
                'ids.*' => ['integer', 'exists:stores,id'],
            ],
            'destroy' => [],
            default => [
                'name' => ['required', 'string', 'max:100'],
                'min_belanja_rmb' => ['required', 'numeric', 'min:0'],
                'ongkir_rmb' => ['required', 'numeric', 'min:0'],
            ],
        };
    }
}
