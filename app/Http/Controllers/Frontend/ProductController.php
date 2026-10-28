<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Product;
use App\Services\Order\ShippingCalculatorService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(private ShippingCalculatorService $shipping) {}

    public function show(Request $request, Game $game, Product $product): View
    {
        abort_unless($product->game_id === $game->id && $product->is_active, 404);

        $product->load(['variants', 'tags', 'merchCategory', 'game']);

        $variant = null;

        if ($request->filled('variant')) {
            $variant = $product->variants->firstWhere('id', (int) $request->query('variant'));
        }

        $pricing = $this->shipping->calculateItemShipping($product, $variant);

        return view('frontend.product.show', [
            'game' => $game,
            'product' => $product,
            'variant' => $variant,
            'pricing' => $pricing,
            'related' => Product::where('is_active', true)
                ->where('game_id', $game->id)
                ->where('id', '!=', $product->id)
                ->inRandomOrder()
                ->limit((int) setting('related_products_count', 6))
                ->with(['game', 'tags'])
                ->get(),
            'shipping' => $this->shipping,
        ]);
    }
}
