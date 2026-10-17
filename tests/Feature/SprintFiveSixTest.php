<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\AdminNotification;
use App\Models\Coin;
use App\Models\Game;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Refund;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SprintFiveSixTest extends TestCase
{
    public function test_checkout_freezes_total_platform_fee_and_initial_payment_expectation(): void
    {
        $user = $this->customer();
        $product = $this->product('priced-merch');
        $cart = $user->carts()->create(['product_id' => $product->id, 'qty' => 1]);

        $this->actingAs($user)->post(route('cart.place-order'), [
            'cart_ids' => [$cart->id],
            'platform' => 'shopee',
            'payment_scheme' => 'fp',
            'payment_method' => 'qris',
        ])->assertRedirect();

        $order = Order::query()->sole();
        $this->assertMatchesRegularExpression('/^MER-[A-Z0-9]{5}-\d{4}$/', $order->order_no);
        $this->assertSame('menunggu_pembayaran', $order->status);
        $this->assertSame(109.0, (float) $order->fee_marketplace);
        $this->assertSame(0.0, (float) $order->fee_qris);
        $this->assertSame(0, Payment::query()->count());
        $this->assertSame(1, $order->statusLogs()->count());
        $this->assertSame(1, UserNotification::query()->where('user_id', $user->id)->count());
        $this->assertSame(1, AdminNotification::query()->where('type', 'new_order')->count());
    }

    public function test_payment_proof_is_locked_until_admin_rejects_or_verifies_it(): void
    {
        Storage::fake('local');
        $user = $this->customer();
        $admin = $this->customer('admin@example.test', '628123456780', ['role' => 'admin']);
        $order = $this->order($user);

        $this->actingAs($user)->post(route('orders.payment-proof', $order), [
            'proof' => UploadedFile::fake()->create('proof.jpg', 100, 'image/jpeg'),
        ])->assertRedirect();

        $payment = Payment::query()->sole();
        $this->assertTrue($payment->is_locked);
        $this->assertSame('ditahan', $order->fresh()->status);
        Storage::disk('local')->assertExists($payment->proof_path);
        $this->assertDatabaseHas('admin_notifications', ['type' => 'payment_proof']);
        $this->actingAs($user)->get(route('office.payments.proof', $payment))->assertForbidden();
        $this->actingAs($admin)->get(route('office.payments.proof', $payment))
            ->assertOk()
            ->assertHeader('content-type', 'image/jpeg');

        $this->patch(route('office.payments.reject', $payment), [
            'rejection_reason' => 'Bukti tidak menampilkan nominal transfer.',
        ])->assertRedirect();
        $this->assertSame('menunggu_pembayaran', $order->fresh()->status);
        $this->assertFalse($payment->fresh()->is_locked);
        $this->assertSame('rejected', $payment->fresh()->status);

        $this->actingAs($user)->post(route('orders.payment-proof', $order), [
            'proof' => UploadedFile::fake()->create('proof-again.jpg', 100, 'image/jpeg'),
        ])->assertRedirect();
        $secondPayment = Payment::query()->where('status', 'pending')->where('is_locked', true)->sole();
        $this->actingAs($admin)->patch(route('office.payments.verify', $secondPayment))->assertRedirect();

        $this->assertSame('pembayaran_diterima', $order->fresh()->status);
        $this->assertSame('verified', $secondPayment->fresh()->status);
        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $user->id,
            'title' => 'Pembayaran diterima',
        ]);
    }

    public function test_refund_request_is_owned_bounded_by_verified_amount_and_notifies_on_completion(): void
    {
        Storage::fake('local');
        $user = $this->customer();
        $admin = $this->customer('refund-admin@example.test', '628123456781', ['role' => 'admin']);
        $other = $this->customer('other-refund@example.test', '628123456782');
        $order = $this->order($user, OrderStatus::PEMBAYARAN_DITERIMA);
        $proofPath = 'payment-proofs/test/proof.jpg';
        Storage::disk('local')->put($proofPath, 'proof-content');
        $order->payments()->create([
            'type' => 'full',
            'amount' => 200000,
            'method' => 'bca',
            'proof_path' => $proofPath,
            'status' => 'verified',
            'verified_at' => now(),
            'verified_by' => $admin->id,
        ]);

        $this->actingAs($other)->post(route('orders.refunds.store', $order), [
            'amount' => 100000,
            'reason' => 'Barang rusak saat diterima',
        ])->assertNotFound();

        $this->actingAs($user)->post(route('orders.refunds.store', $order), [
            'amount' => 250000,
            'reason' => 'Barang rusak saat diterima',
        ])->assertSessionHasErrors('amount');

        $this->post(route('orders.refunds.store', $order), [
            'amount' => 100000,
            'reason' => 'Sebagian barang rusak',
        ])->assertRedirect();
        $refund = Refund::query()->sole();
        $this->assertSame('partial', $refund->type);
        $this->assertDatabaseHas('admin_notifications', ['type' => 'refund_request']);
        $this->assertDatabaseHas('user_notifications', ['subtype' => 'refund_requested']);

        $this->actingAs($admin)->patch(route('office.refunds.process', $refund), [
            'decision' => 'approve',
            'admin_note' => 'Dana telah ditransfer.',
        ])->assertRedirect();

        $this->assertSame('completed', $refund->fresh()->status);
        $this->assertSame(OrderStatus::DANA_DIKEMBALIKAN->value, $order->fresh()->status);
        $this->assertDatabaseHas('user_notifications', ['subtype' => 'refund_completed']);
        Storage::disk('local')->assertMissing($proofPath);
        $this->assertNull($order->payments()->firstOrFail()->fresh()->proof_path);
    }

    public function test_admin_status_change_sets_pickup_timer_timeline_and_h1_notification_is_idempotent(): void
    {
        $user = $this->customer();
        $admin = $this->customer('status-admin@example.test', '628123456783', ['role' => 'admin']);
        $order = $this->order($user, OrderStatus::SAMPAI_WH_INDONESIA);

        $this->actingAs($admin)->patch(route('office.orders.status', $order), [
            'status' => OrderStatus::MENUNGGU_PELUNASAN->value,
        ])->assertRedirect();
        $order->refresh();
        $this->assertSame(OrderStatus::MENUNGGU_PELUNASAN->value, $order->status);
        $this->assertNotNull($order->pickup_deadline_at);
        $this->assertLessThan(1, abs(now()->addDays(7)->diffInMinutes($order->pickup_deadline_at)));
        $this->assertDatabaseHas('user_notifications', ['subtype' => 'pickup_h7']);

        $order->update(['pickup_deadline_at' => now()->addHours(23)->addMinutes(30)]);
        $this->artisan('orders:send-pickup-reminders')->assertSuccessful();
        $this->artisan('orders:send-pickup-reminders')->assertSuccessful();

        $this->assertSame(1, UserNotification::query()
            ->where('user_id', $user->id)
            ->where('subtype', 'pickup_h1')
            ->count());
        $this->assertDatabaseHas('admin_notifications', ['type' => 'order_nearing_expiry']);
        $this->assertSame(1, $order->statusLogs()->count());
    }

    public function test_expired_pickup_orders_are_marked_hangus_and_cannot_be_refunded(): void
    {
        Storage::fake('local');
        $user = $this->customer();
        $admin = $this->customer('expiry-admin@example.test', '628123456779', ['role' => 'admin']);
        $order = $this->order($user, OrderStatus::MENUNGGU_PELUNASAN);
        $path = 'payment-proofs/expired/proof.jpg';
        Storage::disk('local')->put($path, 'private-payment-proof');
        $order->payments()->create([
            'type' => 'dp',
            'amount' => 50000,
            'method' => 'bca',
            'proof_path' => $path,
            'status' => 'verified',
            'verified_at' => now()->subDays(8),
            'verified_by' => $admin->id,
        ]);
        $order->update(['pickup_deadline_at' => now()->subMinute()]);

        $this->artisan('orders:hangus-expired')->assertSuccessful();

        $this->assertSame(OrderStatus::HANGUS->value, $order->fresh()->status);
        $this->assertNotNull($order->fresh()->hangus_at);
        Storage::disk('local')->assertMissing($path);
        $this->assertNull($order->payments()->firstOrFail()->fresh()->proof_path);
        $this->assertTrue(UserNotification::query()
            ->where('user_id', $user->id)
            ->where('meta->status', OrderStatus::HANGUS->value)
            ->exists());
        $this->actingAs($user)->post(route('orders.refunds.store', $order), [
            'amount' => 10000,
            'reason' => 'Refund setelah pesanan hangus',
        ])->assertSessionHasErrors('order');
    }

    public function test_admin_order_screen_and_invalid_bulk_transition_are_supported(): void
    {
        $admin = $this->customer('orders-admin@example.test', '628123456778', ['role' => 'admin']);
        $firstUser = $this->customer('orders-one@example.test', '628123456777');
        $secondUser = $this->customer('orders-two@example.test', '628123456776');
        $first = $this->order($firstUser, OrderStatus::MENUNGGU_PEMBAYARAN);
        $second = $this->order($secondUser, OrderStatus::SELESAI);

        $this->actingAs($admin)->get(route('office.orders.index'))->assertOk()->assertSee($first->order_no);
        $this->get(route('office.orders.show', $first))->assertOk()->assertSee('Ubah Status');
        $this->patch(route('office.orders.bulk-status'), [
            'order_ids' => [$first->id, $second->id],
            'status' => OrderStatus::DITAHAN->value,
        ])->assertSessionHasErrors('status');

        $this->assertSame(OrderStatus::MENUNGGU_PEMBAYARAN->value, $first->fresh()->status);
        $this->assertSame(OrderStatus::SELESAI->value, $second->fresh()->status);
    }

    public function test_dashboard_notifications_and_snapshot_viewer_are_scoped_to_the_signed_in_user(): void
    {
        Storage::fake('public');
        $user = $this->customer();
        $other = $this->customer('another@example.test', '628123456784');
        $order = $this->order($user);
        $item = $order->items()->create(['qty' => 1]);
        Storage::disk('public')->put('order-snapshots/order/item.jpg', 'snapshot-image');
        $snapshot = $item->snapshot()->create([
            'product_id_audit' => 999,
            'product_name' => 'Produk snapshot',
            'product_slug' => 'produk-snapshot',
            'product_sku' => 'SNAP-001',
            'product_image_path' => 'order-snapshots/order/item.jpg',
            'final_price' => 100000,
            'subtotal' => 100000,
        ]);
        UserNotification::create([
            'user_id' => $user->id,
            'type' => 'order',
            'title' => 'Pesanan diperbarui',
            'message' => 'Status pesanan diperbarui.',
            'link_url' => route('orders.show', $order),
        ]);
        Coin::create(['user_id' => $user->id, 'balance' => 15]);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Total Pesanan')
            ->assertSee('Tagihan Menunggu')
            ->assertSee('Pesanan Saya')
            ->assertSee('15');
        $this->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('Produk snapshot')
            ->assertSee('Riwayat Status');
        $this->get(route('orders.snapshots.image', [$order, $snapshot, 'product']))->assertOk();

        $other->forceFill(['email_verified_at' => now()])->save();
        $this->actingAs($other)->get(route('orders.show', $order))->assertNotFound();
        $this->get(route('orders.snapshots.image', [$order, $snapshot, 'product']))->assertNotFound();

        $this->actingAs($user)->get(route('notifications.unread-count'))->assertOk()->assertJson(['count' => 1]);
        $notification = UserNotification::query()->where('user_id', $user->id)->sole();
        $this->post(route('notifications.open', $notification))->assertRedirect(route('orders.show', $order));
        $this->assertTrue($notification->fresh()->is_read);
        $this->get(route('notifications.unread-count'))->assertJson(['count' => 0]);
    }

    private function customer(
        string $email = 'sprint-five-six@example.test',
        string $phone = '628123456789',
        array $overrides = [],
    ): User {
        return User::factory()->create([
            'email' => $email,
            'no_hp' => $phone,
            'email_verified_at' => now(),
            'tgl_lahir' => '1995-01-01',
            'alamat_lengkap' => 'Jl. Merdeka nomor 10',
            'kecamatan' => 'Kebayoran Baru',
            'kota' => 'Jakarta Selatan',
            'provinsi' => 'DKI Jakarta',
            'kode_pos' => '12110',
            ...$overrides,
        ]);
    }

    private function product(string $slug): Product
    {
        $game = Game::create([
            'name' => "Game {$slug}",
            'slug' => "{$slug}-game",
            'is_active' => true,
        ]);
        $product = Product::create([
            'name' => "Product {$slug}",
            'slug' => $slug,
            'sku' => strtoupper($slug),
            'type' => 'single',
            'availability' => 'tersedia',
            'weight_gram' => 250,
            'is_active' => true,
            'price_rmb' => 58,
        ]);
        $product->games()->attach($game);

        return $product;
    }

    private function order(User $user, OrderStatus $status = OrderStatus::MENUNGGU_PEMBAYARAN): Order
    {
        return $user->orders()->create([
            'order_no' => 'MER-'.strtoupper(substr(md5($user->email.$status->value), 0, 5)).'-'.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT),
            'status' => $status->value,
            'payment_scheme' => 'fp',
            'platform' => 'toco',
            'payment_method' => 'bca',
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'customer_phone' => $user->no_hp,
            'customer_address' => 'Jl. Merdeka, Jakarta',
            'subtotal' => 200000,
            'total' => 200000,
        ]);
    }
}
