<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\HeroSlide;
use App\Models\Product;
use App\Support\PriceCalculator;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request): View
    {
        $calculator = PriceCalculator::fromSettings();
        
        return view('home', [
            'user' => $request->user(),
            // Mengambil banner slider yang aktif
            'slides' => HeroSlide::where('is_active', true)->orderBy('sort_order')->get(),
            // Mengambil 10 game terpopuler untuk section "Jelajahi Game"
            'games' => Game::orderBy('sort_order')->take(10)->get(),
            // Mengambil 8 produk terbaru untuk section "Merch Baru"
            'latestProducts' => Product::published()
                ->with(['images', 'variants', 'shippingTier'])
                ->latest()
                ->take(8)
                ->get(),
            'calculator' => $calculator,
        ]);
    }
}
