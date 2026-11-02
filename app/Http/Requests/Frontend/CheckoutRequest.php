<?php

namespace App\Http\Requests\Frontend;

use App\Models\Cart;
use App\Models\Order;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class CheckoutRequest extends FormRequest
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
            'cart_item_ids' => ['required', 'array', 'min:1', function (string $attribute, mixed $value, Closure $fail): void {
                $ids = array_unique(array_filter((array) $value, 'is_numeric'));

                if (Cart::whereIn('id', $ids)->count() !== count($ids)) {
                    $fail(__('validation.exists', ['attribute' => $attribute]));
                }
            }],
            'cart_item_ids.*' => ['integer'],
            'platform' => ['required', 'in:toco,shopee'],
            'scheme' => ['required', 'in:fp,dp'],
            'payment_method' => ['required', 'in:bca,qris,shopeepay'],
            'discount_type' => ['nullable', 'in:none,coin,voucher'],
            'coin_amount' => ['nullable', 'integer', 'min:0'],
            'user_voucher_id' => ['nullable', 'integer', 'exists:user_vouchers,id'],
            'customer_note' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_note.max' => 'Catatan pembelian maksimal 500 karakter.',
        ];
    }

    /**
     * Koin dan voucher tidak boleh dipakai bersamaan (PRD 20.6: "pilih salah satu").
     */
    protected function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $type = $this->input('discount_type');

            if ($this->filled('user_voucher_id') && $type !== Order::DISCOUNT_VOUCHER) {
                $validator->errors()->add('user_voucher_id', 'Koin dan voucher tidak bisa digunakan bersamaan.');
            }

            if ($this->filled('coin_amount') && $type !== Order::DISCOUNT_COIN) {
                $validator->errors()->add('coin_amount', 'Koin dan voucher tidak bisa digunakan bersamaan.');
            }

            if ($type === Order::DISCOUNT_COIN && ! $this->filled('coin_amount')) {
                $validator->errors()->add('coin_amount', 'Jumlah koin wajib diisi.');
            }

            if ($type === Order::DISCOUNT_VOUCHER && ! $this->filled('user_voucher_id')) {
                $validator->errors()->add('user_voucher_id', 'Pilih voucher yang akan dipakai.');
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function checkoutData(): array
    {
        return [
            'platform' => $this->input('platform'),
            'scheme' => $this->input('scheme'),
            'payment_method' => $this->input('payment_method'),
            'discount_type' => $this->input('discount_type'),
            'coin_amount' => $this->input('coin_amount') !== null ? (int) $this->input('coin_amount') : null,
            'user_voucher_id' => $this->input('user_voucher_id') !== null ? (int) $this->input('user_voucher_id') : null,
            'customer_note' => $this->input('customer_note'),
        ];
    }
}
