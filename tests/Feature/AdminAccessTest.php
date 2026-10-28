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

test('semua route modul admin ditolak untuk guest dan customer', function () {
    $urls = [
        '/office',
        '/office/products',
        '/office/products/create',
        '/office/games',
        '/office/developers',
        '/office/stores',
        '/office/merch-categories',
        '/office/tags',
        '/office/orders',
        '/office/payments',
        '/office/users',
        '/office/users/create',
        '/office/settings',
        '/office/reports',
        '/office/admin-logs',
        '/office/auth-logs',
        '/office/activity-logs',
    ];

    $customer = User::factory()->create(['role' => 'customer']);

    foreach ($urls as $url) {
        $this->get($url)->assertRedirect('/login');
    }

    $this->actingAs($customer);

    foreach ($urls as $url) {
        $this->get($url)->assertForbidden();
    }
});
