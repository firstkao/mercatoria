<?php

namespace App\Http\Controllers;

use App\Models\Marketplace;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Support\PriceCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        return view('orders.index', ['orders' => DB::table('orders')->where('user_id', auth()->id())->latest()->paginate(20)]);
    }

    public function show(string $orderNumber): View
    {
        $order = DB::table('orders')->where('user_id', auth()->id())->where('order_number', $orderNumber)->firstOrFail();
        $items = DB::table('order_items')->where('order_id', $order->id)->get();
        $proofs = DB::table('payment_proofs')->where('order_id', $order->id)->latest()->get();
        $methods = DB::table('payment_methods')->where('is_active', true)->orderBy('sort_order')->get();
        return view('orders.show', compact('order', 'items', 'proofs', 'methods'));
    }

    public function checkout(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'marketplace_id' => ['required', 'integer', 'exists:marketplaces,id'],
            'payment_scheme' => ['required', 'in:dp,fp'],
        ]);

        $order = DB::transaction(function () use ($data): object {
            $rows = DB::table('cart_items')->where('user_id', auth()->id())->lockForUpdate()->get();
            abort_if($rows->isEmpty(), 422, 'Keranjang kosong.');
            $calculator = PriceCalculator::fromSettings();
            abort_unless($calculator->isConfigured(), 422, 'Harga belum dikonfigurasi admin.');
            $marketplace = Marketplace::query()->whereKey($data['marketplace_id'])->where('is_active', true)->firstOrFail();
            $subtotal = 0;
            $snapshots = [];

            foreach ($rows as $row) {
                $variant = ProductVariant::query()->with('product.shippingTier')->findOrFail($row->product_variant_id);
                abort_unless($variant->product->is_published && $variant->isAvailable(), 422, 'Ada produk yang sudah tidak tersedia.');
                $unit = $variant->sellingPrice($calculator);
                abort_unless($unit !== null, 422, 'Harga produk belum tersedia.');
                $line = $unit * $row->quantity;
                $subtotal += $line;
                $snapshots[] = [$variant, $row->quantity, $unit, $line];
            }

            $dpPercent = $data['payment_scheme'] === 'dp' ? 50 : 100;
            $payNow = (int) ceil($subtotal * $dpPercent / 100);
            $remaining = $subtotal - $payNow;
            $marketplaceFee = (int) $marketplace->fp_fee_idr + ($data['payment_scheme'] === 'dp' ? (int) round($remaining * ((float) $marketplace->dp_fee_percent / 100)) : 0);
            $number = 'MC-'.now()->format('ymdHis').'-'.strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));

            $orderId = DB::table('orders')->insertGetId([
                'order_number' => $number, 'user_id' => auth()->id(), 'marketplace_id' => $marketplace->id,
                'status' => 'menunggu_pembayaran', 'payment_scheme' => $data['payment_scheme'],
                'subtotal_idr' => $subtotal, 'discount_type' => 'none', 'discount_idr' => 0,
                'total_idr' => $subtotal, 'pay_now_idr' => $payNow, 'remaining_idr' => $remaining,
                'marketplace_fee_idr' => $marketplaceFee, 'coin_estimate' => (int) floor($subtotal / 100),
                'pricing_snapshot' => json_encode(['exchange_rate' => Setting::get('exchange_rate'), 'marketplace' => $marketplace->name], JSON_THROW_ON_ERROR),
                'payment_deadline_at' => now()->addHours(Setting::integer('payment_deadline_hours', 24)),
                'created_at' => now(), 'updated_at' => now(),
            ]);

            foreach ($snapshots as [$variant, $quantity, $unit, $line]) {
                DB::table('order_items')->insert([
                    'order_id' => $orderId, 'product_variant_id' => $variant->id,
                    'product_name_snapshot' => $variant->product->name, 'variant_name_snapshot' => $variant->name,
                    'price_yuan_snapshot' => $variant->price_yuan, 'weight_grams_snapshot' => $variant->weight_grams,
                    'cn_shipping_yuan_snapshot' => 0, 'unit_price_idr' => $unit, 'quantity' => $quantity,
                    'line_total_idr' => $line, 'created_at' => now(), 'updated_at' => now(),
                ]);
            }

            DB::table('order_status_history')->insert(['order_id' => $orderId, 'from_status' => null, 'to_status' => 'menunggu_pembayaran', 'changed_by' => 'user', 'created_at' => now()]);
            DB::table('cart_items')->where('user_id', auth()->id())->delete();
            return DB::table('orders')->where('id', $orderId)->first();
        });

        return redirect()->route('orders.show', $order->order_number)->with('status', 'Order dibuat. Silakan bayar dan unggah bukti pembayaran.');
    }

    public function proof(Request $request, string $orderNumber): RedirectResponse
    {
        $data = $request->validate(['payment_method_id' => ['required', 'integer', 'exists:payment_methods,id'], 'amount_idr' => ['required', 'integer', 'min:1'], 'proof' => ['required', 'image', 'max:5120']]);
        $order = DB::table('orders')->where('user_id', auth()->id())->where('order_number', $orderNumber)->firstOrFail();
        abort_unless($order->status === 'menunggu_pembayaran', 422);
        $path = $request->file('proof')->store('payment-proofs', 'public');
        DB::table('payment_proofs')->insert(['order_id' => $order->id, 'payment_method_id' => $data['payment_method_id'], 'amount_idr' => $data['amount_idr'], 'proof_path' => $path, 'status' => 'pending', 'uploaded_at' => now(), 'created_at' => now(), 'updated_at' => now()]);
        return back()->with('status', 'Bukti pembayaran berhasil diunggah dan menunggu verifikasi admin.');
    }
}
