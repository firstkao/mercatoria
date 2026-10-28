<?php

use App\Models\User;

test('user dapat logout', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/logout')
        ->assertRedirect('/login');

    $this->assertGuest();
});

test('guest yang logout tetap diarahkan tanpa error', function () {
    $this->post('/logout')->assertRedirect('/login');

    $this->assertGuest();
});
