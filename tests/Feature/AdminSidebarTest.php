<?php

use App\Models\User;

test('sidebar admin menandai menu aktif dari route dan menyimpan posisi scroll', function () {
    $admin = User::factory()->admin()->create();

    $html = $this->actingAs($admin)->get('/office/stores')->assertOk()->getContent();

    expect($html)->toContain('admin-sidebar-scroll')
        ->and($html)->toMatch('/<a href="[^"]*\/office\/stores"[^>]*nav-link-active/')
        ->and(substr_count($html, 'nav-link-active') - substr_count($html, '.nav-link-active'))->toBeGreaterThan(0);
});
