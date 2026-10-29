<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\CoinTransaction;
use App\Models\Product;
use App\Models\ShareClick;
use App\Services\User\CoinWalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Stevebauman\Location\Facades\Location;

class ShareController extends Controller
{
    /**
     * PRD 9.5: share link produk — 5 koin per IP unik per link, khusus IP Indonesia.
     */
    public function store(Request $request, Product $product, CoinWalletService $coins): RedirectResponse
    {
        $ip = (string) $request->ip();

        // PRD 9.5: deteksi negara via geo-IP (stevebauman/location).
        try {
            $position = Location::get($ip);
            $country = strtoupper((string) ($position?->countryCode ?? ''));
        } catch (\Throwable) {
            $country = '';
        }

        $alreadyClicked = ShareClick::query()
            ->where('product_id', $product->id)
            ->where('ip_address', $ip)
            ->exists();

        ShareClick::create([
            'user_id' => $request->user()?->id,
            'product_id' => $product->id,
            'ip_address' => $ip,
            'country_code' => $country !== '' ? $country : null,
        ]);

        if (! $alreadyClicked && $country === 'ID' && $request->user()) {
            $coins->credit(
                $request->user(),
                5,
                null,
                CoinTransaction::TYPE_EARNED_SHARE,
                'Share produk '.$product->slug.' (IP unik)'
            );
        }

        return redirect()->back();
    }
}
