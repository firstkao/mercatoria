<?php

namespace App\Http\Requests\Admin;

use App\Models\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;

class OrderStatusFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:'.implode(',', array_map(
                fn (OrderStatus $status): string => $status->value,
                OrderStatus::cases()
            ))],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
