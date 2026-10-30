<?php

use App\Models\Customer;
use App\Models\User;

test('product show kirim data kuota view untuk user crush', function () {
    $user = User::factory()->create(['role' => 'crush']);
    $product = productFinal255();

    $this->actingAs($user)
        ->get('/'.$product->game->slug.'/'.$product->slug)
        ->assertOk()
        ->assertViewHas('userState', 'crush')
        ->assertViewHas('kuotaViewUsed', 1)
        ->assertViewHas('kuotaViewTotal', 10);
});

test('product show tanpa kuota view untuk customer', function () {
    $user = User::factory()->create();
    Customer::factory()->create(['user_id' => $user->id, 'current_state' => 'MVP']);
    $product = productFinal255();

    $this->actingAs($user)
        ->get('/'.$product->game->slug.'/'.$product->slug)
        ->assertOk()
        ->assertViewHas('kuotaViewUsed', null)
        ->assertViewHas('kuotaViewTotal', null);
});
