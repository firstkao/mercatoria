<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemSnapshot;
use App\Models\OrderStatus;
use App\Models\Product;
use App\Models\User;
use App\Services\Order\SnapshotImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InjectOrderController extends Controller
{
    /**
     * PRD 22.7: Input Order Lama — order historis tanpa bukti bayar.
     * Harga snapshot bisa diedit manual; status bebas; WAJIB admin_logs.
     */
    public function index(): View
    {
        return view('admin.orders.inject', [
            'users' => User::orderBy('name')->get(['id', 'name', 'email', 'no_hp']),
            'products' => Product::with('variants')->where('is_active', true)->orderBy('name')->get(),
            'statuses' => array_keys(OrderStatus::transitions()),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        // Baris item kosong dari form diabaikan sebelum validasi.
        $request->merge([
            'items' => array_values(array_filter(
                (array) $request->input('items', []),
                fn (array $item) => filled($item['product_id'] ?? null)
            )),
        ]);

        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'status' => ['required', 'string', Rule::in(array_map(
                fn ($status) => $status->value,
                OrderStatus::cases()
            ))],
            // PRD 48.2: tanggal order historis (boleh kosong → waktu sekarang).
            'tanggal_order' => ['nullable', 'date', 'before_or_equal:now'],
            'customer_note' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:999'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
        ]);

        $user = User::findOrFail($data['user_id']);

        $order = DB::transaction(function () use ($data, $user) {
            $subtotal = 0.0;

            foreach ($data['items'] as $item) {
                $subtotal += (float) $item['price'] * (int) $item['qty'];
            }

            // Status ditulis LANGSUNG tanpa OrderStatusService —
            // GUARD: order inject = catatan historis, tanpa efek samping
            // (koin/voucher/notif tidak jalan, tier/belanja tidak dihitung).
            $order = Order::create([
                'user_id' => $user->id,
                'no_order' => generate_order_number(),
                'status' => $data['status'],
                'scheme' => 'fp',
                'platform' => 'toco',
                'payment_method' => 'bca',
                'subtotal' => $subtotal,
                'discount_amount' => 0,
                'total' => $subtotal,
                'fee_qris' => 0,
                'tagihan_web' => $subtotal,
                'customer_note' => $data['customer_note'] ?? null,
            ]);

            // PRD 48.2: tanggal order historis ditulis langsung ke timestamps.
            if (! empty($data['tanggal_order'])) {
                $order->forceFill([
                    'created_at' => $data['tanggal_order'],
                    'updated_at' => $data['tanggal_order'],
                ])->save(['timestamps' => false]);
            }

            $snapshotImages = app(SnapshotImageService::class);

            foreach ($data['items'] as $item) {
                $product = Product::with('variants')->findOrFail($item['product_id']);
                $variant = $item['variant_id']
                    ? $product->variants->firstWhere('id', $item['variant_id'])
                    : null;
                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'variant_id' => $variant?->id,
                    'qty' => $item['qty'],
                ]);

                // PRD 22.7: snapshot manual — data terkini dari produk, harga hasil edit admin.
                OrderItemSnapshot::create([
                    'order_item_id' => $orderItem->id,
                    'product_id_audit' => $product->id,
                    'variant_id_audit' => $variant?->id,
                    'item_type' => $variant ? 'variant' : 'single',
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'variant_name' => $variant?->name,
                    'variant_sku' => $variant?->sku,
                    'base_price' => $item['price'],
                    'discount_amount' => 0,
                    'final_price' => $item['price'],
                    'qty' => $item['qty'],
                    'subtotal' => (float) $item['price'] * (int) $item['qty'],
                    'product_image_path' => $snapshotImages->copy($product->image_path, $order->no_order),
                    'variant_image_path' => $snapshotImages->copy($variant?->image_path, $order->no_order),
                    'weight_gram' => $variant?->weight_gram ?? $product->weight_gram,
                    'store_name' => $product->effectiveStore()?->name,
                    'ongkir_lokal_cn_idr' => 0,
                    'ongkir_cn_btm_idr' => 0,
                    'ongkir_btm_jkt_idr' => 0,
                    'ongkir_total_idr' => 0,
                    'snapshot_at' => now(),
                ]);
            }

            return $order;
        });

        AdminLog::record($request->user(), 'create', 'Order', $order->id, null, [
            'user_id' => $user->id,
            'status' => $data['status'],
            'tanggal_order' => $data['tanggal_order'] ?? (string) $order->created_at,
            'items' => count($data['items']),
        ], 'Inject order lama '.$order->no_order);

        return redirect()->route('office.orders.show', $order)->with('status', 'Order lama diinjeksi.');
    }
}
