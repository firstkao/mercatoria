<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\Image\UploadError;
use App\Services\Order\ShippingCalculatorService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ProductFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $action = $this->route()->getActionMethod();
        $product = $this->route('product');

        if (in_array($action, ['store', 'update'], true)) {
            $sku = Rule::unique('products', 'sku');
            $slug = Rule::unique('products', 'slug');

            if ($product instanceof Product) {
                $sku->ignore($product);
                $slug->ignore($product);
            }

            // Produk variable: SKU, harga, dan berat diisi per varian, jadi tidak wajib di level produk.
            $isVariable = $this->input('type') === Product::TYPE_VARIABLE;

            return [
                'game_id' => ['required', 'exists:games,id'],
                'developer_id' => ['nullable', 'exists:developers,id'],
                'store_id' => ['nullable', 'exists:stores,id'],
                'merch_category_id' => ['nullable', 'exists:merch_categories,id'],
                'name' => ['required', 'string', 'max:150'],
                'sku' => [$isVariable ? 'nullable' : 'required', 'string', 'max:64', $sku],
                'slug' => ['nullable', 'string', 'max:170', $slug],
                'price_rmb' => [$isVariable ? 'nullable' : 'required', 'numeric', 'min:0'],
                'weight_gram' => ['nullable', 'integer', 'min:1'],
                'type' => ['required', 'in:single,variable'],
                'discount_amount' => ['nullable', 'numeric', 'min:0'],
                'discount_start' => ['nullable', 'date'],
                'discount_end' => ['nullable', 'date', 'after_or_equal:discount_start'],
                'availability' => ['required', 'in:tersedia,kosong'],
                'is_active' => ['nullable', 'boolean'],
                'is_split_box' => ['nullable', 'boolean'],
                'remove_description_image' => ['nullable', 'boolean'],
                'image_path' => ['nullable', 'string', 'max:255', 'starts_with:products/'],
                'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:6144'],
                'description_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:6144'],
                'tags' => ['nullable', 'array'],
                'tags.*' => ['integer', 'exists:tags,id'],
                'variants' => ['nullable', 'array'],
                'variants.*.price_rmb' => ['nullable', 'numeric', 'min:0'],
                'variants.*.weight_gram' => ['nullable', 'integer', 'min:1'],
                'variants.*.availability' => ['nullable', 'in:tersedia,kosong'],
                'variants.*.image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:6144'],
            ];
        }

        if (in_array($action, ['storeVariant', 'updateVariant'], true)) {
            $variant = $this->route('variant');
            $sku = Rule::unique('product_variants', 'sku');

            if ($variant instanceof ProductVariant) {
                $sku->ignore($variant);
            }

            return [
                'name' => ['required', 'string', 'max:150'],
                'sku' => ['nullable', 'string', 'max:64', $sku],
                'price_rmb' => ['required', 'numeric', 'min:0'],
                'weight_gram' => ['nullable', 'integer', 'min:1'],
                'availability' => ['required', 'in:tersedia,kosong'],
                'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:6144'],
            ];
        }

        if ($action === 'bulkWeight') {
            return [
                'weight_gram' => ['required', 'integer', 'min:1'],
                'ids' => ['required', 'array', 'min:1'],
                'ids.*' => ['integer', 'exists:product_variants,id'],
            ];
        }

        if ($action === 'bulk') {
            return [
                'action' => ['required', 'in:aktifkan,nonaktifkan,hapus,salin'],
                'ids' => ['required', 'array', 'min:1'],
                'ids.*' => ['integer', 'exists:products,id'],
            ];
        }

        throw new \RuntimeException(
            "ProductFormRequest: no rules for action '{$action}'. Check controller method name."
        );
    }

    public function messages(): array
    {
        $messages = [];

        foreach (['image', 'description_image'] as $field) {
            $file = $this->file($field);
            if ($file instanceof UploadedFile && ! $file->isValid()) {
                $messages[$field.'.uploaded'] = 'Upload gagal: '.UploadError::describe($file);
            }
        }

        foreach (array_keys($this->file('variants') ?? []) as $i) {
            $file = $this->file("variants.{$i}.image");
            if ($file instanceof UploadedFile && ! $file->isValid()) {
                $messages["variants.{$i}.image.uploaded"] = 'Upload gagal: '.UploadError::describe($file);
            }
        }

        return $messages;
    }

    public function after(): array
    {
        if (! in_array($this->route()->getActionMethod(), ['store', 'update'], true)) {
            return [];
        }

        return [
            function (Validator $validator): void {
                // Produk variable: berat diisi per varian.
                if ($this->input('type') === Product::TYPE_VARIABLE) {
                    return;
                }

                if ($validator->errors()->has('weight_gram') || $this->filled('weight_gram')) {
                    return;
                }

                if ((int) setting('default_weight_gram', 0) > 0) {
                    return;
                }

                $validator->errors()->add(
                    'weight_gram',
                    'Berat wajib diisi karena default berat belum diset'
                );
            },
            function (Validator $validator): void {
                if ($validator->errors()->hasAny(['price_rmb', 'store_id', 'discount_amount'])) {
                    return;
                }

                $discountAmount = $this->input('discount_amount');

                // Tanpa harga produk (produk variable) tidak ada pembanding untuk nominal diskon.
                if ($discountAmount === null || $this->input('price_rmb') === null) {
                    return;
                }

                $product = new Product([
                    'price_rmb' => $this->input('price_rmb'),
                    'store_id' => $this->input('store_id'),
                    'weight_gram' => $this->input('weight_gram'),
                ]);

                $basePrice = app(ShippingCalculatorService::class)
                    ->calculateItemShipping($product)['base_price_bersih'];

                if ((float) $discountAmount > $basePrice) {
                    $validator->errors()->add(
                        'discount_amount',
                        'Nominal diskon tidak boleh melebihi harga jual produk.'
                    );
                }
            },
        ];
    }
}
