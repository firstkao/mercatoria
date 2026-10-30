<?php

use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

test('admin layout menerima data customerEmailsPaused dari setting', function () {
    $this->actingAs(User::factory()->admin()->create());
    Setting::updateOrCreate(['key' => 'pause_customer_emails'], ['value' => '1', 'type' => 'bool', 'group' => 'email', 'description' => 'test']);
    Cache::forget('app_settings_all');

    $view = view('admin.layouts.admin');
    $view->render();
    expect($view->getData()['customerEmailsPaused'] ?? null)->toBeTrue();

    Setting::where('key', 'pause_customer_emails')->update(['value' => '0']);
    Cache::forget('app_settings_all');

    $viewOff = view('admin.layouts.admin');
    $viewOff->render();
    expect($viewOff->getData()['customerEmailsPaused'] ?? null)->toBeFalse();
});
