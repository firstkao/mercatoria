<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Support\Slugs;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ProductRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $slug = Str::slug((string) ($this->input('slug') ?: $this->input('name')));

        $this->merge([
            'slug' => $slug,
            'is_published' => $this->boolean('is_published'),
            // Row keys are kept so uploaded variant photos stay matched to their row.
            'variants' => (array) $this->input('variants', []),
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:255'],
            'game_id' => ['nullable', 'integer', 'exists:games,id'],
            'developer_id' => ['nullable', 'integer', 'exists:developers,id'],
            'shipping_tier_id' => ['required', 'integer', 'exists:shipping_tiers,id'],
            'tag' => ['nullable', Rule::in(array_keys(Product::TAGS))],
            'description' => ['nullable', 'string', 'max:10000'],
            'sale_starts_at' => ['nullable', 'date', 'required_with:sale_ends_at'],
            'sale_ends_at' => ['nullable', 'date', 'required_with:sale_starts_at', 'after:sale_starts_at'],
            'is_published' => ['boolean'],
            'images' => ['array', 'max:10'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_images' => ['array'],
            'remove_images.*' => ['integer'],
            'main_image' => ['nullable', 'string', 'max:20'],
            'variants' => ['required', 'array', 'min:1', 'max:50'],
            'variants.*.id' => ['nullable', 'integer'],
            'variants.*.name' => ['required', 'string', 'max:255'],
            'variants.*.sku' => ['nullable', 'string', 'max:255'],
            'variants.*.price_yuan' => ['required', 'numeric', 'min:0.01', 'max:9999999'],
            'variants.*.compare_price_yuan' => ['nullable', 'numeric', 'gt:variants.*.price_yuan', 'max:9999999'],
            'variants.*.weight_grams' => ['required', 'integer', 'min:1', 'max:1000000'],
            'variants.*.status' => ['required', Rule::in([ProductVariant::STATUS_AVAILABLE, ProductVariant::STATUS_OUT_OF_STOCK])],
            'variants.*.image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'variants.*.remove_image' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->has('slug')) {
                    return;
                }

                $conflict = Slugs::conflict($this->input('slug'), $this->route('product'));
                if ($conflict !== null) {
                    $validator->errors()->add('slug', $conflict);
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'variants.required' => 'Tambahkan minimal satu varian.',
            'variants.*.compare_price_yuan.gt' => 'Harga coret harus lebih besar dari harga yuan.',
            'sale_ends_at.after' => 'Tanggal akhir sale harus setelah tanggal mulai.',
            'images.*.max' => 'Ukuran foto maksimal 4 MB.',
            'variants.*.image.max' => 'Ukuran foto maksimal 4 MB.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama produk',
            'slug' => 'alamat produk',
            'shipping_tier_id' => 'tier ongkir',
            'sale_starts_at' => 'mulai sale',
            'sale_ends_at' => 'akhir sale',
            'images.*' => 'foto',
            'variants.*.name' => 'nama varian',
            'variants.*.price_yuan' => 'harga yuan',
            'variants.*.compare_price_yuan' => 'harga coret',
            'variants.*.weight_grams' => 'berat',
            'variants.*.status' => 'status varian',
            'variants.*.image' => 'foto varian',
        ];
    }
}