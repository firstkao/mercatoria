<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;

test('guest diarahkan ke login untuk detail order', function () {
    [, $order] = placeTestOrder();

    $this->get("/orders/{$order->no_order}")->assertRedirect('/login');
});

test('user lain tidak bisa melihat order orang lain', function () {
    [, $order] = placeTestOrder();
    $other = User::factory()->create();

    $this->actingAs($other)->get("/orders/{$order->no_order}")->assertForbidden();
});

test('user lain tidak bisa upload bukti ke order orang lain', function () {
    [, $order] = placeTestOrder();
    $other = User::factory()->create();

    $this->actingAs($other)->post("/orders/{$order->no_order}/payments", [
        'proof' => UploadedFile::fake()->image('bukti.jpg'),
    ])->assertForbidden();
});

test('user lain tidak bisa download invoice orang lain', function () {
    [, $order] = placeTestOrder();
    $other = User::factory()->create();

    $this->actingAs($other)->get("/orders/{$order->no_order}/invoice")->assertForbidden();
});

test('admin dapat melihat order siapa pun', function () {
    [, $order] = placeTestOrder();
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get("/orders/{$order->no_order}")->assertOk();
});

test('pemilik order dapat melihat detail ordernya', function () {
    [$user, $order] = placeTestOrder();

    $this->actingAs($user)->get("/orders/{$order->no_order}")
        ->assertOk()
        ->assertSee($order->no_order);
});
