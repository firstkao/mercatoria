<?php

use App\Models\User;

test('stat user baru minggu ini mengecualikan admin', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->create();

    $response = $this->actingAs($admin)->get('/office')->assertOk();

    expect($response->viewData('newUsers'))->toBe(1);
});
