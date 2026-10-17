<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductVariantController extends Controller
{
    public function store(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:64', 'unique:product_variants,sku'],
            'name' => ['nullable', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'availability' => ['required', Rule::in(['tersedia', 'kosong'])],
            'weight_gram' => ['nullable', 'integer', 'min:1', 'max:1000000'],
        ]);

        $product->variants()->create($validated);

        return redirect()->route('office.products.edit', $product)->with('success', 'Varian berhasil ditambahkan.');
    }

    public function updateWeight(Request $request, Product $product, ProductVariant $variant): RedirectResponse
    {
        abort_unless($variant->product_id === $product->id, 404);

        $validated = $request->validate([
            'weight_gram' => ['nullable', 'integer', 'min:1', 'max:1000000'],
        ]);

        $variant->update($validated);

        return redirect()->route('office.products.edit', $product)->with('success', 'Berat varian berhasil disimpan.');
    }

    public function bulkUpdateWeight(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'selected_variant_ids' => ['required', 'array', 'min:1'],
            'selected_variant_ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('product_variants', 'id')->where('product_id', $product->id),
            ],
            'weight_gram' => ['required', 'integer', 'min:1', 'max:1000000'],
        ]);

        $updatedCount = $product->variants()
            ->whereIn('id', $validated['selected_variant_ids'])
            ->update(['weight_gram' => $validated['weight_gram']]);

        return redirect()->route('office.products.edit', $product)
            ->with('variant_weight_updated', $updatedCount)
            ->with('success', "Berat {$updatedCount} varian berhasil diperbarui.");
    }
}
