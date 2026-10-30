<?php

use App\Models\ActivityLog;
use App\Models\AdminLog;
use App\Models\AuthLog;
use App\Models\User;

test('halaman activity log menampilkan data aktivitas', function () {
    $admin = User::factory()->admin()->create();
    ActivityLog::record('login', 'Login berhasil');

    $response = $this->actingAs($admin)->get('/office/activity-logs')
        ->assertOk()
        ->assertViewIs('admin.logs.activity');

    expect($response->viewData('logs')->count())->toBeGreaterThanOrEqual(1);
});

test('halaman auth log menampilkan data autentikasi', function () {
    $admin = User::factory()->admin()->create();
    AuthLog::factory()->create();

    $response = $this->actingAs($admin)->get('/office/auth-logs')
        ->assertOk()
        ->assertViewIs('admin.logs.auth');

    expect($response->viewData('logs')->count())->toBeGreaterThanOrEqual(1);
});

test('halaman admin log menampilkan data aksi admin', function () {
    $admin = User::factory()->admin()->create();
    AdminLog::record($admin, 'create', 'Store', 1, null, ['name' => 'Uji'], 'Toko uji');

    $response = $this->actingAs($admin)->get('/office/admin-logs')
        ->assertOk()
        ->assertViewIs('admin.logs.admin');

    expect($response->viewData('logs')->count())->toBeGreaterThanOrEqual(1);
});
