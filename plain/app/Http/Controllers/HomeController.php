<?php

namespace App\Http\Controllers;

use App\Models\Developer;
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
            'slides' => HeroSlide::where('is_active', true)->orderBy('sort_order')->get(),
            'games' => Game::orderBy('sort_order')->orderBy('name')->take(18)->get(),
            'developers' => Developer::orderBy('name')->take(10)->get(),
            'bestSellerProducts' => Product::query()
                ->bestSellers()
                ->with(['images', 'variants', 'shippingTier'])
                ->take(8)
                ->get(),
            'latestProducts' => Product::published()
                ->with(['images', 'variants', 'shippingTier'])
                ->latest()
                ->take(14)
                ->get(),
            'footerProducts' => Product::query()
                ->published()
                ->bestSellers()
                ->with(['images', 'variants', 'shippingTier'])
                ->take(4)
                ->get(),
            'calculator' => $calculator,
            'title' => 'Beranda',
        ]);
    }
}