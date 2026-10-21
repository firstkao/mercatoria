<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItemSnapshot;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\User;
use App\Services\Cart\CheckoutService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CartCheckoutAuditTest extends TestCase
{
    public function test_qris_fee_threshold_includes_marketplace_fee_in_the_checkout_amount(): void
    {
        Setting::setValue('fee_shopee_fp', 109);
        Setting::setValue('qris_threshold', 500000);
        Setting::setValue('fee_qris_percent', 0.03);
        $cart = new Cart(['qty' => 1]);
        $lines = collect([[
            'cart' => $cart,
            'pricing' => [
                'base_price' => 499900,
                'discount_amount' => 0,
                'final_price' => 499900,
                'shipping' => ['ongkir_total_idr' => 0],
            ],
            'subtotal' => 499900,
        ]]);

        $quote = app(CheckoutService::class)->calculateTotals($lines, 'shopee', 'fp', 'qris');

        $this->assertSame(150.0, $quote['fee_qris']);
        $this->assertSame(505000, $quote['total']);

        $dpQuote = app(CheckoutService::class)->calculateTotals($lines, 'shopee', 'dp', 'bca');

        $this->assertSame(62487.5, $dpQuote['fee_marketplace']);
        $this->assertSame(282500, $dpQuote['dp_amount']);
    }

    public function test_order_detail_shows_contact_delivery_and_marketplace_checkout_amount(): void
    {
        $user = $this->customer();
        $order = $this->order($user, OrderStatus::MENUNGGU_PELUNASAN, [
            'payment_scheme' => 'dp',
            'platform' => 'shopee',
            'fee_marketplace' => 109,
            'referral_source' => 'friend-123',
            'pickup_deadline_at' => now()->addDays(7),
        ]);

        $this->actingAs($user)->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee($user->no_hp)
            ->assertSee('Jl. Audit 123, Jakarta')
            ->assertSee('friend-123')
            ->assertSee('Nominal CO marketplace')
            ->assertSee(format_rupiah((float) $order->total + 109))
            ->assertDontSee('Lunasi &amp; Ambil Sekarang');
    }

    public function test_invoice_renders_snapshot_items_without_the_customer_address(): void
    {
        $user = $this->customer();
        $order = $this->order($user, OrderStatus::MENUNGGU_PEMBAYARAN, [
            'customer_address' => 'Private Address 77, Jakarta',
            'customer_note' => 'Bubble wrap please',
        ]);
        $item = $order->items()->create(['qty' => 1]);
        OrderItemSnapshot::create([
            'order_item_id' => $item->id,
            'product_name' => 'Snapshot Product',
            'product_slug' => 'snapshot-product',
            'product_sku' => 'SNAP-1',
            'base_price' => 200000,
            'final_price' => 185000,
            'discount_amount' => 15000,
            'qty' => 1,
            'subtotal' => 185000,
        ]);
        $order->load(['items.snapshot', 'user.customer']);

        $html = view('orders.invoice', compact('order'))->render();

        $this->assertStringContainsString('Snapshot Product', $html);
        $this->assertStringContainsString('Bubble wrap please', $html);
        $this->assertStringNotContainsString('Private Address 77, Jakarta', $html);
    }

    public function test_proof_upload_rejects_reupload_after_24_hours(): void
    {
        Storage::fake('local');
        $user = $this->customer();
        $expiredOrder = $this->order($user);
        $rejectedPayment = $expiredOrder->payments()->create([
            'type' => 'full',
            'amount' => 100000,
            'method' => 'bca',
            'status' => 'rejected',
            'is_locked' => false,
            'updated_at' => now()->subHours(25),
        ]);
        $rejectedPayment->forceFill(['updated_at' => now()->subHours(25)])->saveQuietly();

        $this->actingAs($user)->post(route('orders.payment-proof', $expiredOrder), [
            'proof' => UploadedFile::fake()->create('proof.png', 100, 'image/png'),
        ])->assertSessionHasErrors('proof');

    }

    public function test_proof_filename_matches_the_document_and_web_settlement_is_blocked(): void
    {
        Storage::fake('local');
        $user = $this->customer();
        $order = $this->order($user);

        $this->actingAs($user)->post(route('orders.payment-proof', $order), [
            'proof' => UploadedFile::fake()->create('proof.jpg', 100, 'image/jpeg'),
        ])->assertRedirect();

        $payment = Payment::query()->sole();
        $this->assertSame($order->order_no.' - full - bca.jpg', basename($payment->proof_path));

        $settlementOrder = $this->order($user, OrderStatus::MENUNGGU_PELUNASAN);
        $this->actingAs($user)->post(route('orders.payment-proof', $settlementOrder), [
            'proof' => UploadedFile::fake()->create('settlement.jpg', 100, 'image/jpeg'),
        ])->assertSessionHasErrors('proof');
        $this->assertSame(1, Payment::query()->count());
    }

    public function test_proof_upload_rejects_non_image_files(): void
    {
        Storage::fake('local');
        $user = $this->customer();
        $order = $this->order($user);

        $this->actingAs($user)->post(route('orders.payment-proof', $order), [
            'proof' => UploadedFile::fake()->create('proof.pdf', 100, 'application/pdf'),
        ])->assertSessionHasErrors('proof');

        $this->assertDatabaseCount('payments', 0);
    }

    private function customer(): User
    {
        return User::factory()->create([
            'role' => 'customer',
            'email_verified_at' => now(),
            'tgl_lahir' => '1995-01-01',
            'no_hp' => '628123456789',
            'alamat_lengkap' => 'Jl. Audit 123',
            'kecamatan' => 'Kebayoran Baru',
        ]);
    }

    private function order(User $user, OrderStatus $status = OrderStatus::MENUNGGU_PEMBAYARAN, array $attributes = []): Order
    {
        return $user->orders()->create([
            'order_no' => 'MER-'.strtoupper(substr(md5($user->email.$status->value.random_int(1, 100000)), 0, 5)).'-0001',
            'status' => $status->value,
            'payment_scheme' => 'fp',
            'platform' => 'toco',
            'payment_method' => 'bca',
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'customer_phone' => $user->no_hp,
            'customer_address' => 'Jl. Audit 123, Jakarta',
            'subtotal' => 200000,
            'total' => 200000,
            ...$attributes,
        ]);
    }
}
