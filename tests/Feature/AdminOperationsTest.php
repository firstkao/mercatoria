<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\AdminLog;
use App\Models\AuthLog;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminOperationsTest extends TestCase
{
    public function test_admin_can_filter_and_export_each_audit_log(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        ActivityLog::create(['action' => 'product_view', 'description' => 'Viewed item']);
        AuthLog::create(['email_attempt' => 'failed@example.com', 'action' => 'login_failed', 'reason' => 'invalid_credentials']);
        AdminLog::create(['admin_id' => $admin->id, 'action' => 'product_updated', 'description' => 'Updated item']);

        $this->actingAs($admin)->get(route('office.logs.activity', ['q' => 'Viewed']))
            ->assertOk()->assertSee('Viewed item');
        $this->get(route('office.logs.auth', ['action' => 'login_failed']))
            ->assertOk()->assertSee('failed@example.com')->assertSee('bg-red-50');
        $this->get(route('office.logs.admin', ['q' => 'Updated']))
            ->assertOk()->assertSee('Updated item');

        foreach (['activity', 'auth', 'admin'] as $kind) {
            $this->get(route('office.logs.export', ['kind' => $kind]))
                ->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
        }
    }

    public function test_admin_search_returns_grouped_results_for_products_orders_and_users(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['name' => 'Searchable Customer', 'email' => 'searchable@example.com']);
        $product = Product::create([
            'name' => 'Searchable Product',
            'slug' => 'searchable-product',
            'sku' => 'SEARCH-001',
        ]);
        $order = $customer->orders()->create([
            'order_no' => 'MER-'.strtoupper(Str::random(12)),
            'status' => 'menunggu_pembayaran',
            'payment_scheme' => 'dp',
            'platform' => 'toco',
            'payment_method' => 'bca',
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'customer_phone' => '628123456789',
            'customer_address' => 'Jalan Merdeka Nomor 1',
            'subtotal' => 1000,
            'total' => 1000,
        ]);

        $response = $this->actingAs($admin)->getJson(route('office.search', ['q' => 'Searchable']));

        $response->assertOk()
            ->assertJsonPath('products.0.url', route('office.products.edit', $product))
            ->assertJsonPath('orders.0.url', route('office.orders.show', $order))
            ->assertJsonPath('users.0.url', route('office.users.show', $customer));
    }
}
