<?php

use App\Models\User;

test('guest diarahkan ke login saat membuka halaman office', function () {
    $this->get('/office')->assertRedirect('/login');
});

test('customer tidak boleh membuka halaman office', function () {
    $user = User::factory()->create(['role' => 'customer']);

    $this->actingAs($user)->get('/office')->assertForbidden();
});

test('admin dapat membuka halaman office', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->get('/office')->assertOk();
});
