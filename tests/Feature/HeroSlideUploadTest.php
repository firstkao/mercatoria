<?php

use App\Models\HeroSlide;
use App\Models\User;
use Illuminate\Http\UploadedFile;

test('upload gambar hero slide tersimpan di storage', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/office/hero-slides', [
        'image' => UploadedFile::fake()->image('hero.jpg'),
        'sort_order' => 1,
        'is_active' => 1,
    ])->assertRedirect(route('office.hero-slides.index'));

    $slide = HeroSlide::firstOrFail();
    $path = (string) $slide->image;

    expect($path)->toStartWith('hero-slides/')
        ->and(file_exists(storage_path('app/private/'.$path)))->toBeTrue();
});

test('hapus hero slide menghapus file gambar', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/office/hero-slides', [
        'image' => UploadedFile::fake()->image('hero-hapus.jpg'),
        'sort_order' => 1,
        'is_active' => 1,
    ])->assertRedirect(route('office.hero-slides.index'));

    $slide = HeroSlide::firstOrFail();
    $path = (string) $slide->image;

    $this->actingAs($admin)->delete("/office/hero-slides/{$slide->id}")->assertRedirect();

    expect(file_exists(storage_path('app/private/'.$path)))->toBeFalse();
});
