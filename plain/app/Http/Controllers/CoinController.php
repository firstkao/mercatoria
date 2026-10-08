<?php

namespace App\Http\Controllers;

use App\Models\CoinLot;
use Illuminate\Http\Request;

class CoinController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Konsisten dengan AccountController::buildStats():
        //   - hitung koin yang remaining > 0
        //   - DAN (tanpa expiry ATAU expiry masih di depan)
        //
        // Sebelumnya query di sini cuma `where('expires_at', '>', now())`,
        // sehingga koin lama tanpa expires_at (NULL) tidak ikut dihitung
        // — angka "Total Koin Aktif" jadi beda dengan yang di dashboard.
        $activeCoins = (int) CoinLot::where('user_id', $user->id)
            ->where('remaining', '>', 0)
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->sum('remaining');

        $coinHistory = CoinLot::where('user_id', $user->id)
            ->latest('earned_at')
            ->paginate(15);

        return view('account.coins.index', compact('activeCoins', 'coinHistory'));
    }
}
