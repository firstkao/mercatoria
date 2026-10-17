<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\HeroSlide;
use App\Models\User;
use App\Models\UserVoucher;
use App\Models\Voucher;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminContentManagementTest extends TestCase
{
    public function test_admin_can_manage_vouchers_without_removing_assigned_voucher_history(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->post(route('office.vouchers.store'), [
            'code' => 'welcome-2026',
            'name' => 'Welcome',
            'type' => 'nominal',
            'value' => 25000,
            'min_purchase' => 100000,
            'per_user_limit' => 1,
            'is_active' => 1,
        ])->assertRedirect(route('office.vouchers.index'));

        $voucher = Voucher::query()->firstOrFail();
        $this->assertSame('WELCOME-2026', $voucher->code);
        $this->get(route('office.vouchers.index'))->assertOk()->assertSee('WELCOME-2026');

        $this->put(route('office.vouchers.update', $voucher), [
            'code' => $voucher->code,
            'name' => 'Welcome updated',
            'type' => 'nominal',
            'value' => 30000,
            'min_purchase' => 100000,
            'per_user_limit' => 1,
            'is_active' => 1,
        ])->assertRedirect(route('office.vouchers.index'));
        $this->assertSame('30000.00', $voucher->fresh()->value);

        $user = User::factory()->create();
        UserVoucher::create(['user_id' => $user->id, 'voucher_id' => $voucher->id, 'status' => 'active']);
        $this->delete(route('office.vouchers.destroy', $voucher))
            ->assertRedirect()
            ->assertSessionHasErrors('voucher');
        $this->assertModelExists($voucher->fresh());
    }

    public function test_admin_manages_announcements_and_hero_slides_and_public_home_uses_active_content(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->post(route('office.announcements.store'), [
            'text' => 'Promo spesial bulan ini',
            'link_url' => '/catalog',
            'is_active' => 1,
        ])->assertRedirect(route('office.announcements.index'));

        $announcement = Announcement::query()->firstOrFail();
        $this->get(route('home'))->assertOk()->assertSee('Promo spesial bulan ini');

        $this->post(route('office.hero-slides.store'), [
            'image' => UploadedFile::fake()->createWithContent(
                'hero.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/pC8AAAAASUVORK5CYII='),
            ),
            'title' => 'Promo pilihan',
            'link_url' => '/catalog',
            'sort_order' => 1,
            'is_active' => 1,
        ])->assertRedirect(route('office.hero-slides.index'));

        $slide = HeroSlide::query()->firstOrFail();
        Storage::disk('public')->assertExists($slide->image_path);
        $this->get(route('home'))->assertOk()->assertSee('Promo pilihan')->assertSee('id="hero-carousel"', false);

        $this->delete(route('office.announcements.destroy', $announcement))
            ->assertRedirect(route('office.announcements.index'));
        $this->delete(route('office.hero-slides.destroy', $slide))
            ->assertRedirect(route('office.hero-slides.index'));
        Storage::disk('public')->assertMissing($slide->image_path);
    }
}
