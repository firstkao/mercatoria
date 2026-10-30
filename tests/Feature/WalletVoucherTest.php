<?php

use App\Models\User;
use App\Models\UserVoucher;

test('wallet menampilkan daftar voucher user', function () {
    $user = User::factory()->create();
    $userVoucher = UserVoucher::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)->get('/profile/wallet')
        ->assertOk()
        ->assertSee($userVoucher->voucher->code)
        ->assertSee('Potong');
});
