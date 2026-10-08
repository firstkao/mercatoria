<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\CoinLot;
use App\Models\Marketplace;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\User;
use App\Support\OrderNumberGenerator;
use App\Support\PriceCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ManualOrderController extends Controller
{
    public function create(): View
    {
        $marketplaces = Marketplace::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'fp_fee_idr', 'dp_fee_percent']);

        // ⚠️ WAJIB disiapkan di controller: @json() di Blade memecah argumen
        // dengan explode(',') sehingga `fn ($m) => ['id' => ..., ...]` di Blade
        // akan terpotong di koma pertama → PHP invalid → "Unclosed '['".
        // Sudah pernah kena di paymentMethodsJs (OrderController) — jangan
        // diulang di sini.
        $marketplacesJs = $marketplaces
            ->map(fn ($m) => [
                'id'             => $m->id,
                'name'           => $m->name,
                'fp_fee_idr'     => (int) $m->fp_fee_idr,
                'dp_fee_percent' => (float) $m->dp_fee_percent,
            ])
            ->keyBy('id')
            ->toArray();

        return view('admin.orders.create', [
            'marketplaces'     => $marketplaces,
            'marketplacesJs'   => $marketplacesJs,
            'statuses'         => OrderStatus::options(),
            'defaultCreatedAt' => now()->format('Y-m-d\TH:i'),
            'defaultDeadline'  => now()->addHours(Setting::integer('payment_deadline_hours', 24))->format('Y-m-d\TH:i'),
            'defaultStatus'    => OrderStatus::Selesai->value,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id'                => ['required', 'integer', 'exists:users,id'],
            'order_number'           => ['nullable', 'string', 'max:40', 'unique:orders,order_number'],
            'created_at'             => ['required', 'date'],
            'status'                 => ['required', Rule::enum(OrderStatus::class)],
            'payment_scheme'         => ['required', 'in:FP,DP'],
            'marketplace_id'         => ['required', 'integer', Rule::exists('marketplaces', 'id')->where('is_active', true)],
            'customer_note'          => ['nullable', 'string', 'max:1000'],
            'payment_deadline_at'    => ['nullable', 'date'],
            'discount_idr'           => ['nullable', 'integer', 'min:0'],
            'effects'                => ['nullable', 'boolean'],
            'notify'                 => ['nullable', 'boolean'],

            'items'                  => ['required', 'array', 'min:1', 'max:50'],
            'items.*.variant_id'     => ['required', 'integer', 'exists:product_variants,id'],
            'items.*.quantity'       => ['required', 'integer', 'min:1', 'max:999'],
            'items.*.unit_price_idr' => ['required', 'integer', 'min:0'],
        ], [
            'items.required'              => 'Minimal satu item harus diisi.',
            'items.*.variant_id.required' => 'Pilih varian produk untuk setiap item.',
            'items.*.quantity.min'        => 'Jumlah minimal 1.',
        ]);

        $calculator = PriceCalculator::fromSettings();

        // ===== Build item snapshot & hitung subtotal =====
        $orderItemsData = [];
        $subtotal = 0;

        foreach ($validated['items'] as $row) {
            $variant = ProductVariant::with('product.shippingTier')->findOrFail($row['variant_id']);
            $qty = (int) $row['quantity'];
            $unitPrice = (int) $row['unit_price_idr'];
            $lineTotal = $unitPrice * $qty;
            $subtotal += $lineTotal;

            $tier = $variant->product->shippingTier;
            $cnShipping = $calculator->chinaShippingYuan(
                (float) ($variant->price_yuan ?? 0),
                $tier ? (float) $tier->fee_yuan : 0.0,
                $tier ? (float) $tier->min_purchase_yuan : 0.0,
            );

            $orderItemsData[] = [
                'product_variant_id'        => $variant->id,
                'product_name_snapshot'     => $variant->product->name,
                'variant_name_snapshot'     => $variant->name,
                'price_yuan_snapshot'       => $variant->price_yuan,
                'weight_grams_snapshot'     => $variant->weight_grams,
                'cn_shipping_yuan_snapshot' => $cnShipping,
                'unit_price_idr'            => $unitPrice,
                'quantity'                  => $qty,
                'line_total_idr'            => $lineTotal,
            ];
        }

        $discountIdr = max(0, (int) ($validated['discount_idr'] ?? 0));
        $netTotal    = max(0, $subtotal - $discountIdr);

        $mp     = Marketplace::findOrFail($validated['marketplace_id']);
        $scheme = $validated['payment_scheme'];

        if ($scheme === 'FP') {
            $mpFee     = (int) $mp->fp_fee_idr;
            $payNow    = $netTotal + $mpFee;
            $remaining = 0;
        } else {
            $payNow        = (int) floor($netTotal / 2);
            $remainingBase = $netTotal - $payNow;
            $mpFee         = (int) floor($remainingBase * $mp->dp_fee_percent / 100);
            $remaining     = $remainingBase + $mpFee;
        }

        $coinEstimate = (int) floor($netTotal * (Setting::integer('coin_earn_percent', 1) / 100));

        // Nomor order: auto-generate kalau kosong
        $orderNumber = $validated['order_number'] ?: OrderNumberGenerator::generate();

        $createdAt = Carbon::parse($validated['created_at']);

        $order = DB::transaction(function () use (
            $validated, $orderItemsData, $subtotal, $discountIdr, $netTotal,
            $mp, $scheme, $payNow, $remaining, $mpFee, $coinEstimate,
            $orderNumber, $createdAt, $calculator
        ) {
            $status = $validated['status'];

            $order = Order::create([
                'order_number'        => $orderNumber,
                'user_id'             => $validated['user_id'],
                'marketplace_id'      => $mp->id,
                'status'              => $status,
                'payment_scheme'      => $scheme,
                'subtotal_idr'        => $subtotal,
                'discount_type'       => $discountIdr > 0 ? 'manual' : 'none',
                'discount_idr'        => $discountIdr,
                'total_idr'           => $netTotal,
                'pay_now_idr'         => $payNow,
                'remaining_idr'       => $remaining,
                'marketplace_fee_idr' => $mpFee,
                'coin_estimate'       => $coinEstimate,
                'pricing_snapshot'    => $calculator->toArray(),
                'payment_deadline_at' => $validated['payment_deadline_at'] ?? null,
                'customer_note'       => $validated['customer_note'] ?? null,
            ]);

            // Override timestamps (biar backdate-able)
            $order->created_at = $createdAt;
            $order->updated_at = $createdAt;

            // Auto-set timestamps berdasarkan status
            if (in_array($status, OrderStatus::revenueValues(), true)) {
                $order->paid_at = $createdAt;
            }
            if ($status === OrderStatus::Selesai->value) {
                $order->completed_at = $createdAt;
            }
            if ($status === OrderStatus::Dibatalkan->value) {
                $order->cancelled_at = $createdAt;
            }
            $order->save();

            foreach ($orderItemsData as $itemData) {
                $order->items()->create($itemData);
            }

            // Riwayat status awal
            DB::table('order_status_history')->insert([
                'order_id'    => $order->id,
                'from_status' => null,
                'to_status'   => $status,
                'changed_by'  => 'admin',
                'admin_id'    => auth('admin')->id(),
                'note'        => 'Order dibuat manual oleh admin',
                'created_at'  => $createdAt,
            ]);

            // ===== Efek samping opsional =====
            $runEffects = (bool) ($validated['effects'] ?? false);
            $silent     = ! (bool) ($validated['notify'] ?? false);

            if ($runEffects && $status === OrderStatus::Selesai->value) {
                $this->awardCompletionRewards($order, $silent);
            }

            return $order;
        });

        AdminLog::record('create_manual_order', $order, [
            'order_number' => $order->order_number,
            'status'       => $order->status,
            'total_idr'    => $order->total_idr,
        ]);

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('status', "Order manual {$order->order_number} berhasil dibuat.");
    }

    /**
     * Endpoint AJAX: cari user untuk autocomplete.
     */
    public function searchUsers(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $users = User::query()
            ->where(function ($sub) use ($q) {
                $sub->where('full_name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('whatsapp', 'like', "%{$q}%");
            })
            ->orderBy('full_name')
            ->limit(15)
            ->get(['id', 'full_name', 'email', 'whatsapp']);

        return response()->json($users->map(fn ($u) => [
            'id'       => $u->id,
            'name'     => $u->full_name,
            'email'    => $u->email,
            'whatsapp' => $u->whatsapp,
        ]));
    }

    /**
     * Endpoint AJAX: cari varian produk untuk autocomplete.
     */
    public function searchVariants(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $calc = PriceCalculator::fromSettings();

        $variants = ProductVariant::query()
            ->with('product:id,name,slug,shipping_tier_id')
            ->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('sku', 'like', "%{$q}%")
                    ->orWhereHas('product', fn ($p) => $p->where('name', 'like', "%{$q}%"));
            })
            ->whereHas('product', fn ($p) => $p->where('is_published', true))
            ->limit(20)
            ->get();

        return response()->json($variants->map(function ($v) use ($calc) {
            $selling = null;
            try {
                $selling = $v->sellingPrice($calc);
            } catch (\Throwable $e) {
                // varian rusak — biarkan null
            }

            return [
                'id'                => $v->id,
                'product_name'      => $v->product->name,
                'variant_name'      => $v->name,
                'sku'               => $v->sku,
                'price_yuan'        => $v->price_yuan,
                'weight_grams'      => $v->weight_grams,
                'selling_price_idr' => $selling,
            ];
        }));
    }

    /**
     * Efek samping saat order manual dibuat dengan status = selesai.
     *
     * Cuma cashback + bonus pertama. Referral di-skip karena order manual
     * biasanya bukan order pertama via kode referral.
     * Notifikasi di-skip kalau $silent = true.
     */
    private function awardCompletionRewards(Order $order, bool $silent): void
    {
        $expiry = now()->addMonths(Setting::integer('coin_expiry_months', 12));

        // 1) Cashback koin
        $cashbackGiven = CoinLot::where('order_id', $order->id)
            ->where('source', 'cashback')
            ->exists();

        if ($order->coin_estimate > 0 && ! $cashbackGiven) {
            CoinLot::create([
                'user_id'    => $order->user_id,
                'source'     => 'cashback',
                'order_id'   => $order->id,
                'amount'     => $order->coin_estimate,
                'remaining'  => $order->coin_estimate,
                'earned_at'  => now(),
                'expires_at' => $expiry,
            ]);

            if (! $silent && $order->user) {
                try {
                    \App\Services\NotificationService::coinsEarned(
                        $order->user,
                        $order->coin_estimate,
                        'cashback'
                    );
                } catch (\Throwable $e) {
                    Log::warning('Gagal kirim notif cashback manual', [
                        'order_id' => $order->id,
                        'error'    => $e->getMessage(),
                    ]);
                }
            }
        }

        // 2) Bonus koin pertama
        $completedCount = Order::where('user_id', $order->user_id)
            ->where('status', OrderStatus::Selesai->value)
            ->count();

        $bonusGiven = CoinLot::where('user_id', $order->user_id)
            ->where('source', 'bonus_pertama')
            ->exists();

        if ($completedCount === 1 && ! $bonusGiven) {
            $bonus = Setting::integer('customer_bonus_coin', 1000);
            if ($bonus > 0) {
                CoinLot::create([
                    'user_id'    => $order->user_id,
                    'source'     => 'bonus_pertama',
                    'order_id'   => $order->id,
                    'amount'     => $bonus,
                    'remaining'  => $bonus,
                    'earned_at'  => now(),
                    'expires_at' => $expiry,
                ]);

                if (! $silent && $order->user) {
                    try {
                        \App\Services\NotificationService::coinsEarned(
                            $order->user,
                            $bonus,
                            'bonus_pertama'
                        );
                    } catch (\Throwable $e) {
                        Log::warning('Gagal kirim notif bonus manual', [
                            'order_id' => $order->id,
                            'error'    => $e->getMessage(),
                        ]);
                    }
                }
            }
        }
    }
}
