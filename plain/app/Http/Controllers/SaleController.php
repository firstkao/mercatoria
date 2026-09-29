<?php

namespace App\Http\Controllers;

use App\Models\Developer;
use App\Models\Game;
use App\Models\Product;
use App\Support\PriceCalculator;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SaleController extends Controller
{
    public function index(Request $request): View
    {
        $calculator = PriceCalculator::fromSettings();

        $games = Game::query()->orderBy('sort_order')->orderBy('name')->get(['id', 'name', 'slug']);
        $developers = Developer::query()->orderBy('name')->get(['id', 'name', 'slug']);

        $selectedGame = $games->firstWhere('slug', $request->query('game'));
        $selectedDeveloper = $developers->firstWhere('slug', $request->query('developer'));

        $products = Product::query()
            ->published()
            ->onSale()
            ->when($selectedGame, fn ($q) => $q->where('game_id', $selectedGame->id))
            ->when($selectedDeveloper, fn ($q) => $q->where('developer_id', $selectedDeveloper->id))
            ->with(['images', 'variants', 'shippingTier', 'game', 'developer'])
            ->orderBy('sale_ends_at')
            ->paginate(24)
            ->withQueryString();

        return view('sale.index', [
            'user' => $request->user(),
            'products' => $products,
            'games' => $games,
            'developers' => $developers,
            'selectedGame' => $selectedGame,
            'selectedDeveloper' => $selectedDeveloper,
            'calculator' => $calculator,
            'title' => 'Sedang Diskon',
            'metaDescription' => 'Daftar merchandise game yang sedang diskon di MERCATORIA. Harga coret terbatas, buruan sebelum masa promo habis.',
        ]);
    }
}