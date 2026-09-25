<?php

namespace App\Http\Controllers;

use App\Models\Developer;
use App\Models\Game;
use App\Models\Product;
use App\Models\Setting;
use App\Support\PriceCalculator;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function index(Request $request): View
    {
        $games = Game::query()->orderBy('sort_order')->orderBy('name')->get(['id', 'name', 'slug']);
        $developers = Developer::query()->orderBy('name')->get(['id', 'name', 'slug']);

        $selectedGame = $games->firstWhere('slug', $request->query('game'));
        $selectedDeveloper = $developers->firstWhere('slug', $request->query('developer'));
        $selectedTag = array_key_exists((string) $request->query('tag'), Product::TAGS) ? $request->query('tag') : null;

        $products = Product::query()
            ->published()
            ->when($selectedGame, fn ($query) => $query->where('game_id', $selectedGame->id))
            ->when($selectedDeveloper, fn ($query) => $query->where('developer_id', $selectedDeveloper->id))
            ->when($selectedTag, fn ($query) => $query->where('tag', $selectedTag))
            ->with(['images', 'variants', 'shippingTier'])
            ->latest()
            ->paginate(24)
            ->withQueryString();

        return view('catalog.index', [
            'user' => $request->user(),
            'products' => $products,
            'games' => $games,
            'developers' => $developers,
            'selectedGame' => $selectedGame,
            'selectedDeveloper' => $selectedDeveloper,
            'selectedTag' => $selectedTag,
            'calculator' => PriceCalculator::fromSettings(),
            'viewQuota' => Setting::integer('view_quota', 10),
        ]);
    }
}