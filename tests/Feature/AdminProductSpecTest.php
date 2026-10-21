<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\Product;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminProductSpecTest extends TestCase
{
    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function product(): Product
    {
        $game = Game::create(['name' => 'Game X', 'slug' => 'game-x', 'is_active' => true]);
        $product = Product::create([
            'name' => 'Produk X', 'slug' => 'produk-x', 'sku' => 'PX',
            'type' => 'variant', 'availability' => 'tersedia', 'weight_gram' => 250,
            'is_active' => true, 'price_rmb' => 58,
        ]);
        $product->games()->attach($game);

        return $product;
    }

    public function test_product_update_syncs_tags_and_validates_discount_and_images(): void
    {
        Storage::fake('public');
        Storage::fake('local');
        $product = $this->product();
        $tag = Tag::create(['name' => 'LIMITED', 'slug' => 'limited', 'color' => '#000000']);
        $payload = [
            'name' => 'Produk X', 'slug' => 'produk-x', 'sku' => 'PX', 'type' => 'variant',
            'availability' => 'tersedia', 'weight_gram' => 250, 'is_active' => 1, 'price_rmb' => 58,
            'game_ids' => [$product->games()->first()->id],
            'tag_ids' => [$tag->id],
        ];

        $this->actingAs($this->admin())->put(route('office.products.update', $product), $payload + [
            'discount_amount' => 99999999,
        ])->assertSessionHasErrors('discount_amount');

        $this->put(route('office.products.update', $product), $payload + [
            'main_image' => UploadedFile::fake()->create('big.jpg', 2000, 'image/jpeg'),
        ])->assertSessionHasErrors('main_image');

        $this->put(route('office.products.update', $product), $payload + ['discount_amount' => 1000])
            ->assertSessionHasNoErrors();
        $this->assertTrue($product->tags()->whereKey($tag->id)->exists());
        $this->assertEquals(1000, $product->fresh()->discount_amount);
    }

    public function test_generate_variants_creates_cartesian_combinations_without_duplicates(): void
    {
        $product = $this->product();
        $this->actingAs($this->admin())
            ->post(route('office.products.variants.generate', $product), ['values' => ['karakter' => 'A, B', 'ukuran' => 'S, M']])
            ->assertSessionHas('success');
        $this->assertSame(4, $product->variants()->count());

        $this->post(route('office.products.variants.generate', $product), ['values' => ['karakter' => 'A, B', 'ukuran' => 'S, M']]);
        $this->assertSame(4, $product->variants()->count());
    }

    public function test_variant_store_defaults_availability_and_saved_changes_update_weights(): void
    {
        $product = $this->product();
        $this->actingAs($this->admin())
            ->post(route('office.products.variants.store', $product), ['sku' => 'PX-1', 'attributes' => ['karakter' => 'Z']])
            ->assertSessionHasNoErrors();
        $variant = $product->variants()->firstOrFail();
        $this->assertSame('tersedia', $variant->availability);

        $this->patch(route('office.products.variants.changes', $product), ['weights' => [$variant->id => 300]]);
        $this->assertSame(300, $variant->fresh()->weight_gram);
    }

    public function test_duplicate_creates_inactive_copy_with_variants(): void
    {
        $product = $this->product();
        $product->variants()->create(['sku' => 'PX-V', 'name' => 'V', 'availability' => 'tersedia']);

        $this->actingAs($this->admin())->post(route('office.products.duplicate', $product))->assertRedirect();
        $copy = Product::where('id', '!=', $product->id)->firstOrFail();
        $this->assertFalse((bool) $copy->is_active);
        $this->assertSame(1, $copy->variants()->count());
    }
}
