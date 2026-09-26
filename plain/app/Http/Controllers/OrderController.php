<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Marketplace;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Support\PriceCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        // Mix: Menggunakan Eloquent ORM yang lebih rapi (Kode 2) 
        // dengan nama view dari (Kode 1)
        $orders = $request->user()->orders()->latest()->paginate(20);
        
        return view('orders.index', compact('orders'));
    }

    public function show(string $orderNumber): View
    {
        // Mix: Cari berdasarkan order_number (Kode 1) tapi menggunakan Eloquent (Kode 2)
        $order = Order::where('user_id', auth()->id())
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        $order->load('items.variant.product', 'paymentProofs.method', 'marketplace');
        
        $paymentMethods = PaymentMethod::where('is_active', true)->orderBy('sort_order')->get();

        return view('orders.show', compact('order', 'paymentMethods'));
    }

    public function proof(Request $request, string $orderNumber): RedirectResponse
    {
        // Mix: Nama method disesuaikan dengan Route (Kode 1), logika menggunakan Kode 2
        $order = Order::where('user_id', auth()->id())
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        abort_unless($order->status === 'menunggu_pembayaran', 422, 'Status pesanan tidak memungkinkan upload bukti.');

        $request->validate([
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
            'amount_idr' => ['required', 'integer', 'min:1'],
            'proof' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'], // Max 5MB
        ]);

        $path = $request->file('proof')->store('payment-proofs', 'public');

        // Simpan bukti bayar menggunakan relasi
        $order->paymentProofs()->create([
            'payment_method_id' => $request->payment_method_id,
            'amount_idr' => $request->amount_idr,
            'proof_path' => $path,
            'status' => 'pending'
        ]);

        // Fitur canggih dari Kode 2: Ubah status pesanan dan catat log
        $order->update(['status' => 'ditahan']);
        
        if (class_exists(ActivityLog::class)) {
            ActivityLog::record(auth()->user(), 'upload_payment_proof', $request, ['order_number' => $order->order_number]);
        }

        return redirect()->route('orders.show', $orderNumber)->with('status', 'Bukti pembayaran berhasil diunggah. Menunggu verifikasi admin.');
    }

    /**
     * CATATAN UNTUK CHECKOUT:
     * Karena di file web.php sebelumnya rute checkout mengarah ke CheckoutController, 
     * kamu bisa CUT (potong) method di bawah ini, pindahkan ke CheckoutController.php, 
     * dan ubah namanya dari checkout() menjadi store().
     */
    public function checkout(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'marketplace_id' => ['required', 'integer', 'exists:marketplaces,id'],
            'payment_scheme' => ['required', 'in:dp,fp'],
        ]);

        $rows = DB::table('cart_items')
            ->where('user_id', auth()->id())
            ->orderBy('id')
            ->get();

        abort_if($rows->isEmpty(), 422, 'Keranjang masih kosong.');

        $calculator = PriceCalculator::fromSettings();
        abort_unless($calculator->isConfigured(), 422, 'Admin belum mengisi rate harga.');

        $marketplace = Marketplace::query()
            ->whereKey($data['marketplace_id'])
            ->where('is_active', true)
            ->firstOrFail();

        $subtotal = 0;
        $items = [];

        DB::transaction(function () use ($rows, $calculator, $marketplace, $data, &$subtotal, &$items): void {
            foreach ($rows as $row) {
                $variant = ProductVariant::query()->with('product.shippingTier')->findOrFail($row->product_variant_id);

                abort_unless($variant->product->is_published && $variant->isAvailable(), 422, 'Ada produk yang tidak tersedia lagi.');

                $unitPrice = $variant->sellingPrice($calculator);
                abort_unless($unitPrice !== null, 422, 'Harga produk belum tersedia.');

                $lineTotal = $unitPrice * (int) $row->quantity;
                $subtotal += $lineTotal;

                $items[] = [
                    'product_variant_id' => $variant->id,
                    'product_name_snapshot' => $variant->product->name,
                    'variant_name_snapshot' => $variant->name,
                    'price_yuan_snapshot' => $variant->price_yuan,
                    'weight_grams_snapshot' => $variant->weight_grams,
                    'cn_shipping_yuan_snapshot' => 0,
                    'unit_price_idr' => $unitPrice,
                    'quantity' => (int) $row->quantity,
                    'line_total_idr' => $lineTotal,
                ];
            }

            $isDp = $data['payment_scheme'] === 'dp';
            $dpPercent = $isDp ? 50 : 100;
            $payNowIdr = (int) ceil($subtotal * $dpPercent / 100);
            $remainingIdr = max(0, $subtotal - $payNowIdr);
            $marketplaceFeeIdr = (int) $marketplace->fp_fee_idr;
            $coinEstimate = (int) floor($subtotal / 100);

            $orderNumber = 'MC-'.now()->format('ymdHis').'-'.strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
            $orderId = DB::table('orders')->insertGetId([
                'order_number' => $orderNumber,
                'user_id' => auth()->id(),
                'marketplace_id' => $marketplace->id,
                'status' => 'menunggu_pembayaran',
                'payment_scheme' => $data['payment_scheme'],
                'subtotal_idr' => $subtotal,
                'discount_type' => 'none',
                'discount_idr' => 0,
                'total_idr' => $subtotal,
                'pay_now_idr' => $payNowIdr,
                'remaining_idr' => $remainingIdr,
                'marketplace_fee_idr' => $marketplaceFeeIdr,
                'coin_estimate' => $coinEstimate,
                'pricing_snapshot' => json_encode([
                    'exchange_rate' => Setting::get('exchange_rate'),
                    'marketplace' => $marketplace->name,
                    'payment_scheme' => $data['payment_scheme'],
                ], JSON_THROW_ON_ERROR),
                'payment_deadline_at' => now()->addHours(Setting::integer('payment_deadline_hours', 24)),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($items as $item) {
                DB::table('order_items')->insert([
                    'order_id' => $orderId,
                    'product_variant_id' => $item['product_variant_id'],
                    'product_name_snapshot' => $item['product_name_snapshot'],
                    'variant_name_snapshot' => $item['variant_name_snapshot'],
                    'price_yuan_snapshot' => $item['price_yuan_snapshot'],
                    'weight_grams_snapshot' => $item['weight_grams_snapshot'],
                    'cn_shipping_yuan_snapshot' => $item['cn_shipping_yuan_snapshot'],
                    'unit_price_idr' => $item['unit_price_idr'],
                    'quantity' => $item['quantity'],
                    'line_total_idr' => $item['line_total_idr'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('order_status_history')->insert([
                'order_id' => $orderId,
                'from_status' => null,
                'to_status' => 'menunggu_pembayaran',
                'changed_by' => 'user',
                'created_at' => now(),
            ]);

            DB::table('cart_items')->where('user_id', auth()->id())->delete();

            $request->session()->flash('last_order_number', $orderNumber);
        });

        return redirect()->route('orders.index')->with('status', 'Order berhasil dibuat. Silakan bayar dan upload bukti pembayaran.');
    }
}
