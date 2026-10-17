<?php

namespace App\Services\User;

use App\Enums\CoinSource;
use App\Enums\NotificationType;
use App\Models\Product;
use App\Models\ShareClick;
use App\Models\User;
use App\Services\Notification\ScheduledEmailService;
use App\Services\Notification\UserNotificationService;
use App\Services\Security\GeoIpCountryResolver;
use Illuminate\Support\Facades\DB;

class ShareClickService
{
    public function __construct(
        private readonly GeoIpCountryResolver $geoIp,
        private readonly CoinWalletService $wallet,
        private readonly ScheduledEmailService $emails,
    ) {}

    public function record(User $referrer, Product $product, string $ipAddress): ?ShareClick
    {
        if (! filter_var($ipAddress, FILTER_VALIDATE_IP)) {
            return null;
        }

        $countryCode = $this->geoIp->countryCode($ipAddress);

        [$click, $rewarded] = DB::transaction(function () use ($referrer, $product, $ipAddress, $countryCode): array {
            $click = ShareClick::query()->firstOrCreate(
                [
                    'user_id' => $referrer->id,
                    'product_id' => $product->id,
                    'ip_address' => $ipAddress,
                ],
                ['country_code' => $countryCode],
            );

            if ($countryCode && $click->country_code !== $countryCode) {
                $click->update(['country_code' => $countryCode]);
            }

            $rewarded = false;
            if ($click->country_code === 'ID' && ! $click->reward_given) {
                $rewarded = $this->wallet->award(
                    $referrer,
                    5,
                    CoinSource::SHARE,
                    "Reward share klik #{$click->id} untuk produk #{$product->id}.",
                );
                $click->update(['reward_given' => true]);
            }

            return [$click, $rewarded];
        });

        if ($rewarded) {
            UserNotificationService::push(
                $referrer->id,
                NotificationType::COIN,
                'Koin bertambah',
                '5 koin ditambahkan karena teman membuka link share produkmu.',
                route('wallet.index'),
                subtype: 'coin_earned',
                meta: ['amount' => 5, 'source' => CoinSource::SHARE->value, 'share_click_id' => $click->id],
            );
            $this->emails->send($referrer, 'coin_earned', 'Koin bertambah', [
                'nama_user' => $referrer->name,
                'jumlah_koin' => '5',
                'link' => route('wallet.index'),
                'tanggal' => now()->format('d-m-Y'),
            ]);
        }

        return $click;
    }
}
