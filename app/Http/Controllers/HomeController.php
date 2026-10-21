<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\HeroSlide;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $visibleProducts = fn (): Builder => Product::query()
            ->active()
            ->whereHas('games', fn (Builder $query) => $query->active())
            ->with(['games', 'tags', 'variants']);

        return view('home', [
            'heroSlides' => HeroSlide::query()->active()->get(),
            'games' => Game::active()->orderBy('name')->get(),
            'bestOffers' => $visibleProducts()->bestOffer(5)->get(),
            'newProducts' => $visibleProducts()->latest()->latest('id')->limit(14)->get(),
        ]);
    }
}
