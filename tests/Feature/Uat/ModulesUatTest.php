<?php

use App\Models\Developer;
use App\Models\Game;
use App\Models\PaymentMethod;
use App\Models\PreorderPage;
use App\Models\User;
use App\Models\UserVoucher;
use App\Models\Voucher;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

test('UAT voucher: CRUD, validasi, issue ke user lalu revoke', function () {
    $this->post('/office/vouchers', [])->assertSessionHasErrors(['code', 'nominal']);
    $this->post('/office/vouchers', ['code' => 'UAT10', 'nominal' => 10000, 'valid_days' => 30])
        ->assertSessionHas('status', 'Voucher template dibuat.');
    $v = Voucher::where('code', 'UAT10')->firstOrFail();

    $this->post('/office/vouchers', ['code' => 'UAT10', 'nominal' => 1])->assertSessionHasErrors('code');
    $this->post('/office/vouchers', ['code' => 'X', 'nominal' => -5])->assertSessionHasErrors('nominal');
    $this->get('/office/vouchers')->assertOk();

    $customer = User::factory()->create();
    $this->post("/office/vouchers/{$v->id}/issue", ['user_id' => $customer->id])->assertSessionHasNoErrors();
    $uv = UserVoucher::where('user_id', $customer->id)->firstOrFail();

    $this->delete("/office/user-vouchers/{$uv->id}")->assertSessionHasNoErrors();
    expect($uv->fresh()?->status)->not->toBe(UserVoucher::STATUS_ACTIVE);

    $this->put("/office/vouchers/{$v->id}", ['code' => 'UAT10', 'nominal' => 20000])
        ->assertSessionHas('status', 'Voucher template diperbarui.');
    expect((float) $v->fresh()->nominal)->toBe(20000.0);
});

test('UAT payment method: create, validasi tipe, update, delete', function () {
    $this->post('/office/payment-methods', [])->assertSessionHasErrors(['code', 'name']);
    $this->post('/office/payment-methods', ['code' => 'x', 'name' => 'X', 'type' => 'kripto'])->assertSessionHasErrors('type');

    $this->post('/office/payment-methods', ['code' => 'uatbank', 'name' => 'Bank UAT', 'type' => 'bank_transfer', 'account_number' => '123'])
        ->assertRedirect('/office/payment-methods')->assertSessionHas('status', 'Metode bayar dibuat.');
    $m = PaymentMethod::where('code', 'uatbank')->firstOrFail();
    $this->get('/office/payment-methods')->assertOk()->assertSee('Bank UAT');

    $this->putJson("/office/payment-methods/{$m->id}", ['code' => 'uatbank', 'name' => 'Bank UAT 2', 'type' => 'bank_transfer'])->assertSuccessful();
    expect($m->fresh()->name)->toBe('Bank UAT 2');
    $this->deleteJson("/office/payment-methods/{$m->id}")->assertSuccessful();
    $this->assertModelMissing($m);
});

test('UAT developer dan game: create, validasi, update, delete (tanpa upload)', function () {
    $this->post('/office/developers', [])->assertSessionHasErrors('name');
    $this->post('/office/developers', ['name' => 'Studio UAT', 'is_active' => 1])->assertSessionHasNoErrors();
    $dev = Developer::where('name', 'Studio UAT')->firstOrFail();
    $this->get('/office/developers')->assertOk()->assertSee('Studio UAT');

    $this->post('/office/games', [])->assertSessionHasErrors('name');
    $this->post('/office/games', ['name' => 'Game UAT', 'is_active' => 1, 'developers' => [$dev->id]])->assertSessionHasNoErrors();
    $game = Game::where('name', 'Game UAT')->firstOrFail();
    $this->get('/office/games')->assertOk()->assertSee('Game UAT');

    $this->put("/office/games/{$game->id}", ['name' => 'Game UAT 2', 'is_active' => 1])->assertSessionHasNoErrors();
    expect($game->fresh()->name)->toBe('Game UAT 2');

    $this->delete("/office/games/{$game->id}");
    $this->delete("/office/developers/{$dev->id}");
    $this->assertModelMissing($game);
    $this->assertModelMissing($dev);
});

test('UAT preorder page: validasi, draft lalu publish tampil publik', function () {
    $this->post('/office/preorder', [])->assertSessionHasErrors(['title', 'slug']);
    $this->get('/office/preorder/create')->assertOk();

    $this->post('/office/preorder', ['title' => 'PO UAT', 'slug' => 'po-uat', 'content' => '<p>Halo PO</p>', 'status' => 'draft'])
        ->assertSessionHasNoErrors();
    $page = PreorderPage::where('slug', 'po-uat')->firstOrFail();
    $this->get("/office/preorder/{$page->id}/edit")->assertOk();

    auth()->logout();
    $this->get('/po-uat')->assertNotFound();

    $this->actingAs($this->admin)->put("/office/preorder/{$page->id}", ['title' => 'PO UAT', 'slug' => 'po-uat', 'content' => '<p>Halo PO</p>', 'status' => 'publish'])
        ->assertSessionHasNoErrors();
    auth()->logout();
    $this->get('/po-uat')->assertOk()->assertSee('Halo PO', false);
});

test('UAT laporan: export CSV berisi header dan order', function () {
    [, $order] = placeTestOrder();

    $response = $this->get('/office/reports/export')->assertOk();
    $csv = $response->streamedContent();

    expect($response->headers->get('content-type'))->toContain('text/csv')
        ->and($csv)->toContain('no_order')->toContain($order->no_order);
});

test('UAT laporan: halaman laporan terbuka', function () {
    $this->get('/office/reports')->assertOk();
    $this->get('/office/reports/sales-chart')->assertOk();
});
