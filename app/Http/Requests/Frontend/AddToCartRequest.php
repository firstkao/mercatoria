<?php

namespace App\Http\Requests\Frontend;

use App\Models\Product;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class AddToCartRequest extends FormRequest
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
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'qty' => ['required', 'integer', 'min:1', 'max:999'],
        ];
    }

    protected function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $product = Product::find($this->input('product_id'));
            $variantId = $this->input('variant_id');

            if ($product && ! $product->is_active) {
                $validator->errors()->add('product_id', 'Produk tidak tersedia.');
            }

            if ($variantId && $product && ! $product->variants()->where('id', $variantId)->exists()) {
                $validator->errors()->add('variant_id', 'Varian bukan milik produk ini.');
            }

            if ($product && ! $variantId && $product->type === Product::TYPE_VARIANT) {
                $validator->errors()->add('variant_id', 'Pilih varian untuk produk ini.');
            }
        });
    }
}
