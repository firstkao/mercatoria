<?php

namespace App\Http\Controllers;

use App\Models\Marketplace;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\SplitBox;
use App\Models\SplitBoxSlot;
use App\Support\OrderNumberGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SplitBoxCheckoutController extends Controller
{
    public function checkout(Request $request, Product $product): RedirectResponse
    {
        abort_unless($product->isSplitBox(), 404);

        $splitBox = $product->splitBox()->firstOrFail();
        abort_unless($splitBox->isPublished(), 422, 'Split box sudah tidak aktif.');

        $user = $request->user();

        $request->validate([
            'slot_ids'   => ['required', 'array', 'min:1'],
            'slot_ids.*' => ['integer', 'exists:split_box_slots,id'],
        ]);

        $slotIds = array_unique($request->input('slot_ids'));

        return DB::transaction(function () use ($user, $splitBox, $product, $slotIds) {
            $slots = SplitBoxSlot::whereIn('id', $slotIds)
                ->where('split_box_id', $splitBox->id)
                ->lockForUpdate()
                ->get();

            if ($slots->count() !== count($slotIds)) {
                throw ValidationException::withMessages(['slot_ids' => 'Ada slot yang tidak ditemukan.']);
            }

            foreach ($slots as $slot) {
                if (! $slot->isAvailable()) {
                    throw ValidationException::withMessages([
                        'slot_ids' => "Slot {$slot->character_name} sudah terisi.",
                    ]);
                }
            }

            $this->validateBundleRule($slots, $splitBox);

            $unitPrice = (int) $splitBox->price_per_slot_idr;
            $subtotal  = $unitPrice * $slots->count();

            $mp = Marketplace::where('is_active', true)->first();
            if (! $mp) {
                throw ValidationException::withMessages(['checkout' => 'Marketplace tidak tersedia.']);
            }

            $mpFee  = (int) $mp->fp_fee_idr;
            $payNow = $subtotal + $mpFee;

            // Variant dummy untuk order item — pakai variant pertama product
            $defaultVariant = $product->variants()->first();
            if (! $defaultVariant) {
                throw ValidationException::withMessages([
                    'checkout' => 'Produk split box belum punya variant default. Hubungi admin.',
                ]);
            }

            $orderNumber = OrderNumberGenerator::generate();

            $order = Order::create([
                'user_id'             => $user->id,
                'order_number'        => $orderNumber,
                'status'              => 'menunggu_pembayaran',
                'subtotal_idr'        => $subtotal,
                'total_idr'           => $subtotal,
                'discount_idr'        => 0,
                'pay_now_idr'         => $payNow,
                'remaining_idr'       => 0,
                'coin_estimate'       => (int) floor($subtotal * (Setting::integer('coin_earn_percent', 1) / 100)),
                'payment_scheme'      => 'FP',
                'marketplace_id'      => $mp->id,
                'marketplace_fee_idr' => $mpFee,
                'customer_note'       => 'Split Box: ' . $product->name,

                'recipient_name'       => $user->full_name,
                'recipient_phone'      => $user->whatsapp,
                'recipient_email'      => $user->email,
                'shipping_address'     => $user->street_address,
                'shipping_city'        => $user->city,
                'shipping_province'    => $user->province,
                'shipping_postal_code' => $user->postal_code,
            ]);

            // 1 slot = 1 order item
            foreach ($slots as $slot) {
                $order->items()->create([
                    'product_variant_id'    => $defaultVariant->id,
                    'product_name_snapshot' => $product->name,
                    'variant_name_snapshot' => $slot->character_name,
                    'price_yuan_snapshot'   => 0,
                    'weight_grams_snapshot' => $defaultVariant->weight_grams ?? 0,
                    'cn_shipping_yuan_snapshot' => 0,
                    'unit_price_idr'        => $unitPrice,
                    'quantity'              => 1,
                    'line_total_idr'        => $unitPrice,
                    'split_box_slot_id'     => $slot->id,
                ]);

                $slot->update([
                    'status'            => SplitBoxSlot::STATUS_LOCKED,
                    'locked_by_user_id' => $user->id,
                    'locked_at'         => now(),
                ]);
            }

            return redirect()
                ->route('account.orders.show', $order->order_number)
                ->with('status', "Order {$order->order_number} dibuat! Upload bukti bayar untuk konfirmasi slot.");
        });
    }

    private function validateBundleRule($selectedSlots, SplitBox $splitBox): void
    {
        $allSlots = $splitBox->slots()->get();
        $selectedIds = $selectedSlots->pluck('id')->all();

        $bundleSelected     = $selectedSlots->where('bundle_required', true)->count();
        $standaloneSelected = $selectedSlots->where('bundle_required', false)->count();

        $remainingStandalone = $allSlots
            ->where('bundle_required', false)
            ->where('status', SplitBoxSlot::STATUS_AVAILABLE)
            ->whereNotIn('id', $selectedIds)
            ->count();

        $remainingBundle = $allSlots
            ->where('bundle_required', true)
            ->where('status', SplitBoxSlot::STATUS_AVAILABLE)
            ->whereNotIn('id', $selectedIds)
            ->count();

        $unmatchedBundle = ($bundleSelected - $standaloneSelected) + $remainingBundle;

        if ($unmatchedBundle > $remainingStandalone) {
            throw ValidationException::withMessages([
                'slot_ids' => 'Kombinasi tidak valid — butuh lebih banyak slot standalone untuk pasangan bundle.',
            ]);
        }
    }
}
