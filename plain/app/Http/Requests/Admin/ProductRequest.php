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
            'is_featured' => $this->boolean('is_featured'),
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
            'is_featured' => ['boolean'],
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
            'variants.*.compare_price_yuan' => ['nullable', 'numeric', 'max:9999999'],
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
            // Validasi per-item: harga coret wajib > harga yuan. Rule wildcard
            // `gt:variants.*.price_yuan` tidak di-resolve per-item oleh Laravel,
            // jadi perbandingannya dilakukan manual di sini.
            function (Validator $validator): void {
                foreach ((array) $this->input('variants', []) as $index => $variant) {
                    $price = $variant['price_yuan'] ?? null;
                    $compare = $variant['compare_price_yuan'] ?? null;

                    if ($price === null || $compare === null || $compare === '') {
                        continue;
                    }

                    if (! is_numeric($price) || ! is_numeric($compare)) {
                        continue;
                    }

                    if ((float) $compare <= (float) $price) {
                        $validator->errors()->add(
                            "variants.{$index}.compare_price_yuan",
                            'Harga coret harus lebih besar dari harga yuan.'
                        );
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
        return [
            'variants.required' => 'Tambahkan minimal satu varian.',
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
            'variants.*.name' => 'nama variasi',
            'variants.*.price_yuan' => 'harga yuan',
            'variants.*.compare_price_yuan' => 'harga coret',
            'variants.*.weight_grams' => 'berat',
            'variants.*.status' => 'status varian',
            'variants.*.image' => 'foto varian',
        ];
    }
}