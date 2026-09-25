<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use App\Support\PriceCalculator;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'stats' => [
                'Produk tayang' => Product::query()->published()->count(),
                'Draf produk' => Product::query()->where('is_published', false)->count(),
                'Spammer' => User::query()->where('role', UserRole::Spammer)->count(),
                'Customer' => User::query()->where('role', UserRole::Customer)->count(),
                'Pendaftar reseller baru' => DB::table('reseller_applications')->where('status', 'pending')->count(),
                'Bukti pembayaran pending' => DB::table('payment_proofs')->where('status', 'pending')->count(),
            ],
            'ratesConfigured' => PriceCalculator::fromSettings()->isConfigured(),
        ]);
    }
}
