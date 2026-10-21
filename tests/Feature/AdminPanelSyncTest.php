<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_and_sidebar_pages_render_for_admin_only(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($customer)->get(route('office.dashboard'))->assertForbidden();
        $this->actingAs($admin)->get(route('office.dashboard'))
            ->assertOk()->assertSee('Dashboard')->assertSee('Top 5 Produk Terjual');

        foreach (['office.merch-categories.index', 'office.tags.index', 'office.developers.index', 'office.payments.index', 'office.customers.rank'] as $name) {
            $this->get(route($name))->assertOk();
        }
        $this->getJson(route('office.notifications.unread-count'))->assertOk()->assertJsonStructure(['count', 'items']);
    }

    public function test_user_create_requires_valid_province(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('office.users.create'))->assertOk()->assertSee('<select', false);
        $this->post(route('office.users.store'), ['provinsi' => 'Atlantis'])->assertSessionHasErrors('provinsi');
    }

    public function test_email_toggle_is_logged_with_reason_and_emits_ga4_event(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('office.settings.edit', ['tab' => 'email']))
            ->assertOk()->assertSee('Catatan / Alasan Stop Email');

        $this->patch(route('office.settings.update'), ['pause_customer_emails' => 1, 'pause_reason' => 'Testing migrasi'])
            ->assertSessionHas('ga4_events', fn ($e) => $e[0]['name'] === 'admin_toggle_customer_email');

        $this->assertSame('1', Setting::getValue('pause_customer_emails'));
        $this->assertDatabaseHas('admin_logs', ['admin_id' => $admin->id, 'action' => 'toggle_customer_email']);
        $this->get(route('office.settings.edit'))->assertSee('EMAIL CUSTOMER: OFF');
    }

    public function test_pricing_fee_and_watermark_settings_are_saved(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->patch(route('office.settings.update'), [
            'ongkir_cn_btm_per_kg' => 25000,
            'ongkir_btm_jkt_per_kg' => 60000,
            'default_weight_gram' => 0,
            'fee_shopee_fp' => 120,
            'image_watermark_text' => 'MERC',
            'image_watermark_pattern' => 'diagonal_tiled',
            'image_watermark_heavy_direct' => 1,
        ])->assertSessionHasNoErrors();

        $this->assertSame('120', Setting::getValue('fee_shopee_fp'));
        $this->assertSame('MERC', Setting::getValue('image_watermark_text'));
        $this->assertSame('1', Setting::getValue('image_watermark_heavy_direct'));
    }
}
