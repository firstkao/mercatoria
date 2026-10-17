<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Order;
use App\Models\OrderItemSnapshot;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LegacyOrderImportController extends Controller
{
    public function create(Request $request): View
    {
        $filters = $request->validate([
            'user_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where('role', 'customer')->where('is_migrated', true),
            ],
        ]);

        return view('admin.orders.import', [
            'users' => User::query()->where('role', 'customer')->where('is_migrated', true)->orderBy('name')->get(),
            'products' => Product::query()->with('variants')->orderBy('name')->get(),
            'selectedUserId' => $filters['user_id'] ?? null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where('role', 'customer')->where('is_migrated', true),
            ],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'variant_id' => [
                'nullable',
                'integer',
                Rule::exists('product_variants', 'id')->where('product_id', $request->input('product_id')),
            ],
            'qty' => ['required', 'integer', 'min:1', 'max:10000'],
            'unit_price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'ordered_at' => ['required', 'date', 'before_or_equal:now'],
        ]);

        [$order, $user] = DB::transaction(function () use ($validated): array {
            $user = User::query()->where('role', 'customer')->where('is_migrated', true)->findOrFail($validated['user_id']);
            $product = Product::query()->with(['games.developers', 'tags'])->findOrFail($validated['product_id']);
            $variant = isset($validated['variant_id'])
                ? ProductVariant::query()->with('combinations')->findOrFail($validated['variant_id'])
                : null;
            $orderedAt = now()->parse($validated['ordered_at']);
            $unitPrice = (float) $validated['unit_price'];
            $subtotal = $unitPrice * $validated['qty'];
            $address = collect([
                $user->alamat_lengkap,
                $user->kecamatan,
                $user->kota,
                $user->provinsi,
                $user->kode_pos,
            ])->filter()->join(', ');

            $order = Order::create([
                'order_no' => 'OLD-'.Str::upper(Str::random(16)),
                'user_id' => $user->id,
                'status' => OrderStatus::SELESAI->value,
                'payment_scheme' => 'fp',
                'platform' => 'legacy',
                'payment_method' => 'legacy_import',
                'customer_name' => $user->name,
                'customer_email' => $user->email,
                'customer_phone' => $user->no_hp,
                'customer_address' => $address,
                'subtotal' => $subtotal,
                'shipping_cost' => 0,
                'total' => $subtotal,
                'admin_note' => 'Order lama hasil migrasi. Rincian ongkir historis tidak tersedia.',
            ]);
            $order->forceFill(['created_at' => $orderedAt, 'updated_at' => $orderedAt])->save();

            $item = $order->items()->create([
                'product_id' => $product->id,
                'variant_id' => $variant?->id,
                'qty' => $validated['qty'],
            ]);

            $game = $product->games->first();
            $snapshot = OrderItemSnapshot::create([
                'order_item_id' => $item->id,
                'product_id_audit' => $product->id,
                'variant_id_audit' => $variant?->id,
                'product_name' => $product->name,
                'product_slug' => $product->slug,
                'product_sku' => $product->sku,
                'game_name' => $game?->name,
                'developer_name' => $game?->developers->first()?->name,
                'store_name' => $product->effectiveStore()?->name,
                'tag' => $product->tags->first()?->name,
                'variant_name' => $variant?->name,
                'variant_sku' => $variant?->sku,
                'variant_attributes' => $variant?->attributesArray() ?? [],
                'base_price' => $unitPrice,
                'discount_amount' => 0,
                'final_price' => $unitPrice,
                'qty' => $validated['qty'],
                'subtotal' => $subtotal,
                'weight_gram' => $variant?->effectiveWeight() ?? $product->effectiveWeight(),
                'ongkir_lokal_cn_idr' => 0,
                'ongkir_cn_btm_idr' => 0,
                'ongkir_btm_jkt_idr' => 0,
                'ongkir_total_idr' => 0,
                'product_image_path' => $product->main_image_path,
                'variant_image_path' => $variant?->image_path,
                'snapshot_at' => $orderedAt,
            ]);

            AdminLog::record(
                'legacy_order_import',
                Order::class,
                $order->id,
                new: ['order_no' => $order->order_no, 'snapshot_id' => $snapshot->id],
                description: 'Admin mengimpor order lama dari WooCommerce.',
            );

            return [$order, $user];
        });

        return redirect()->route('office.users.show', $user)
            ->with('success', "Order lama {$order->order_no} berhasil disimpan sebagai snapshot.");
    }
}
