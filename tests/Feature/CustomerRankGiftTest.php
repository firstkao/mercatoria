<?php

use App\Models\Coin;
use App\Models\Customer;
use App\Models\User;
use App\Models\UserGift;
use App\Models\UserVoucher;
use App\Models\Voucher;
use App\Notifications\UserGiftNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

function rankCustomer(array $customer = [], array $user = []): User
{
    $u = User::factory()->create($user);
    Customer::factory()->create(['user_id' => $u->id] + $customer);

    return $u;
}

test('rank hanya menampilkan customer, urut total belanja, bisa filter & export', function () {
    $admin = User::factory()->admin()->create();
    rankCustomer(['total_belanja' => 500000], ['name' => 'Budi Besar']);
    rankCustomer(['total_belanja' => 100000], ['name' => 'Kecil Kia']);
    $crush = User::factory()->create(['role' => User::ROLE_CRUSH, 'name' => 'Crush Dewi']);
    Customer::factory()->create(['user_id' => $crush->id]);

    $this->actingAs($admin)->get(route('office.customers.rank'))
        ->assertOk()->assertSeeInOrder(['Budi Besar', 'Kecil Kia'])->assertDontSee('Crush Dewi');

    $this->actingAs($admin)->get(route('office.customers.rank', ['q' => 'Kecil']))
        ->assertSee('Kecil Kia')->assertDontSee('Budi Besar');

    $this->actingAs($admin)->get(route('office.customers.rank.export'))
        ->assertOk()->assertDownload();
});

test('rank hanya untuk admin', function () {
    $this->actingAs(rankCustomer())->get(route('office.customers.rank'))->assertForbidden();
});

test('gift koin menambah saldo, mencatat log dan mengirim notifikasi', function () {
    Notification::fake();
    $admin = User::factory()->admin()->create();
    $target = rankCustomer();

    $this->actingAs($admin)->post(route('office.users.gift', $target), ['type' => 'koin', 'coin_amount' => 500, 'note' => 'Terima kasih'])
        ->assertSessionHas('status');

    expect(Coin::where('user_id', $target->id)->value('balance'))->toBe(500);
    $this->assertDatabaseHas('user_gifts', ['user_id' => $target->id, 'type' => UserGift::TYPE_COIN, 'coin_amount' => 500]);
    $this->assertDatabaseHas('admin_logs', ['action' => 'gift']);
    $this->assertDatabaseHas('user_notifications', ['user_id' => $target->id, 'type' => 'admin_gift']);
    Notification::assertSentTo($target, UserGiftNotification::class);
});

test('gift voucher menerbitkan user voucher; gift box hanya dicatat', function () {
    Notification::fake();
    $admin = User::factory()->admin()->create();
    $target = rankCustomer();
    $voucher = Voucher::factory()->create();

    $this->actingAs($admin)->post(route('office.users.gift', $target), ['type' => 'voucher', 'voucher_id' => $voucher->id])->assertSessionHas('status');
    expect(UserVoucher::where('user_id', $target->id)->where('voucher_id', $voucher->id)->count())->toBe(1);

    $this->actingAs($admin)->post(route('office.users.gift', $target), ['type' => 'gift_box'])->assertSessionHas('status');
    $this->assertDatabaseHas('user_gifts', ['user_id' => $target->id, 'type' => UserGift::TYPE_GIFT_BOX]);
});

test('gift validasi: nominal wajib, bukan untuk crush, bukan oleh non-admin', function () {
    $admin = User::factory()->admin()->create();
    $target = rankCustomer();

    $this->actingAs($admin)->post(route('office.users.gift', $target), ['type' => 'koin'])->assertSessionHasErrors('coin_amount');
    $this->actingAs($admin)->post(route('office.users.gift', $target), ['type' => 'koin', 'coin_amount' => 0])->assertSessionHasErrors('coin_amount');

    $crush = User::factory()->create(['role' => User::ROLE_CRUSH]);
    $this->actingAs($admin)->post(route('office.users.gift', $crush), ['type' => 'gift_box'])->assertNotFound();

    $this->actingAs($target)->post(route('office.users.gift', $target), ['type' => 'gift_box'])->assertForbidden();
});
