<?php

use App\Models\Page;
use App\Models\User;
use App\Rules\PublicSlug;
use Illuminate\Support\Facades\Validator;

function slugErrors(string $slug): int
{
    return Validator::make(['slug' => $slug], ['slug' => [new PublicSlug]])->errors()->count();
}

test('PublicSlug menolak slug yang bentrok dengan route sistem atau format salah', function () {
    expect(slugErrors('login'))->toBe(1)
        ->and(slugErrors('Halaman Baru'))->toBe(1)
        ->and(slugErrors('promo-lebaran'))->toBe(0)
        ->and(slugErrors('tnc'))->toBe(0);
});

test('admin tidak bisa membuat halaman dengan slug route sistem', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/office/pages', [
        'title' => 'Login', 'slug' => 'login', 'content' => 'x', 'status' => 'publish',
    ])->assertSessionHasErrors('slug');

    expect(Page::where('slug', 'login')->exists())->toBeFalse();
});
