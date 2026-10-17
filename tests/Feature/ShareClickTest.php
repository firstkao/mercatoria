<?php

namespace Tests\Feature;

use App\Enums\CoinSource;
use App\Models\EmailTemplate;
use App\Models\Game;
use App\Models\Product;
use App\Models\ShareClick;
use App\Models\User;
use App\Services\Security\GeoIpCountryResolver;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Mockery;
use Tests\TestCase;

class ShareClickTest extends TestCase
{
    public function test_product_referral_rewards_five_coins_once_per_indonesian_ip_and_link(): void
    {
        $game = $this->createGame();
        $product = $this->createProduct($game, 'share-product');
        $referrer = User::factory()->create(['referral_code' => 'REFERRER1']);
        EmailTemplate::create([
            'code' => 'coin_earned',
            'name' => 'Koin Bertambah',
            'from_email' => 'info@mercatoria.id',
            'subject' => 'Koin bertambah',
            'body' => '<p>{{jumlah_koin}}</p>',
            'variables' => ['jumlah_koin'],
            'is_active' => true,
        ]);
        Mail::fake();
        $resolver = Mockery::mock(GeoIpCountryResolver::class);
        $resolver->shouldReceive('countryCode')
            ->twice()
            ->with('203.0.113.20')
            ->andReturn('ID');
        $this->app->instance(GeoIpCountryResolver::class, $resolver);
        $url = route('games.products.show', [
            'game' => $game->slug,
            'product' => $product->slug,
            'ref' => $referrer->referral_code,
        ]);

        $server = [
            'REMOTE_ADDR' => '203.0.113.20',
            'HTTP_X_FORWARDED_FOR' => '198.51.100.20',
        ];
        $this->withServerVariables($server)->get($url)->assertOk();
        $this->withServerVariables($server)->get($url)->assertOk();

        $click = ShareClick::query()->sole();
        $this->assertSame('ID', $click->country_code);
        $this->assertTrue($click->reward_given);
        $this->assertSame(5, (int) $referrer->coin()->value('balance'));
        $this->assertDatabaseHas('coin_transactions', [
            'user_id' => $referrer->id,
            'amount' => 5,
            'source' => CoinSource::SHARE->value,
        ]);
        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $referrer->id,
            'subtype' => 'coin_earned',
        ]);
        $this->assertDatabaseHas('email_logs', [
            'recipient_email' => $referrer->email,
            'status' => 'sent',
        ]);
    }

    public function test_foreign_share_click_is_tracked_without_coin_reward_and_owner_click_is_ignored(): void
    {
        $game = $this->createGame();
        $product = $this->createProduct($game, 'foreign-share-product');
        $referrer = User::factory()->create(['referral_code' => 'REFERRER2']);
        $resolver = Mockery::mock(GeoIpCountryResolver::class);
        $resolver->shouldReceive('countryCode')->once()->andReturn('US');
        $this->app->instance(GeoIpCountryResolver::class, $resolver);
        $url = route('games.products.show', [
            'game' => $game->slug,
            'product' => $product->slug,
            'ref' => $referrer->referral_code,
        ]);

        $this->get($url)->assertOk();
        $this->assertDatabaseHas('share_clicks', [
            'user_id' => $referrer->id,
            'product_id' => $product->id,
            'country_code' => 'US',
            'reward_given' => false,
        ]);
        $this->assertDatabaseMissing('coins', ['user_id' => $referrer->id]);

        $this->actingAs($referrer)->get($url)->assertOk();
        $this->assertSame(1, ShareClick::query()->count());
    }

    public function test_missing_geoip_database_tracks_click_without_reward_and_logs_configuration_error(): void
    {
        $game = $this->createGame();
        $product = $this->createProduct($game, 'unknown-share-product');
        $referrer = User::factory()->create(['referral_code' => 'REFERRER3']);
        config()->set('services.geoip.database_path', null);
        Log::shouldReceive('critical')
            ->once()
            ->with('Local GeoIP country database is unavailable; share rewards are disabled.');
        $url = route('games.products.show', [
            'game' => $game->slug,
            'product' => $product->slug,
            'ref' => $referrer->referral_code,
        ]);

        $this->get($url)->assertOk();

        $this->assertDatabaseHas('share_clicks', [
            'user_id' => $referrer->id,
            'product_id' => $product->id,
            'country_code' => null,
            'reward_given' => false,
        ]);
        $this->assertDatabaseMissing('coins', ['user_id' => $referrer->id]);
    }

    private function createGame(): Game
    {
        return Game::create([
            'name' => 'Share Game',
            'slug' => 'share-game',
            'is_active' => true,
        ]);
    }

    private function createProduct(Game $game, string $slug): Product
    {
        $product = Product::create([
            'name' => "Product {$slug}",
            'slug' => $slug,
            'sku' => strtoupper($slug),
            'type' => 'single',
            'availability' => 'tersedia',
            'weight_gram' => 250,
            'is_active' => true,
            'price_rmb' => 58,
        ]);
        $product->games()->attach($game);

        return $product;
    }
}
