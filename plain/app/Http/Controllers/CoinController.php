<?php

namespace App\Http\Controllers;

use App\Models\CoinLot;
use Illuminate\Http\Request;

class CoinController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Hitung total koin yang masih aktif dan belum kedaluwarsa
        $activeCoins = CoinLot::where('user_id', $user->id)
            ->where('expires_at', '>', now())
            ->sum('remaining');

        // Ambil riwayat perolehan koin
        $coinHistory = CoinLot::where('user_id', $user->id)
            ->latest('earned_at')
            ->paginate(15);

        return view('account.coins.index', compact('activeCoins', 'coinHistory'));
    }
}
