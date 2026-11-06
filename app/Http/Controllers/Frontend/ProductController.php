<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Product;
use App\Services\Order\ShippingCalculatorService;
use App\Services\User\UserStateService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        private ShippingCalculatorService $shipping,
        private UserStateService $userState,
    ) {}

    public function show(Request $request, Game $game, Product $product): View
    {
        abort_unless($product->game_id === $game->id && $product->is_active, 404);

        $user = $request->user();
        if ($user) {
            $this->userState->recordView($user, $product);
        }

        $product->load([
            'variants.attributeValues.attribute',
            'attributes.values',
            'tags',
            'merchCategory',
            'game',
            'developer',
        ]);

        $variant = null;
        if ($request->filled('variant')) {
            $variant = $product->variants->firstWhere('id', (int) $request->query('variant'));
        }

        $pricing = $this->shipping->calculateItemShipping($product, $variant);

        $userState = $user ? $this->userState->stateName($user) : null;
        $viewUsage = $user && $this->userState->isViewLimited($user)
            ? $this->userState->viewUsage($user)
            : null;

        $relatedLimit = (int) setting('related_products_count', 6);
        $related = $relatedLimit > 0
            ? Product::where('is_active', true)
                ->where('game_id', $game->id)
                ->where('id', '!=', $product->id)
                ->inRandomOrder()
                ->limit($relatedLimit)
                ->with(['game', 'tags'])
                ->get()
            : collect();

        return view('frontend.product.show', [
            'game' => $game,
            'product' => $product,
            'variant' => $variant,
            'pricing' => $pricing,
            'userState' => $userState,
            'kuotaViewUsed' => $viewUsage['used'] ?? null,
            'kuotaViewTotal' => $viewUsage['total'] ?? null,
            'related' => $related,
            'shipping' => $this->shipping,
        ]);
    }
}
