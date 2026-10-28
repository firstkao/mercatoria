<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\AddToCartRequest;
use App\Models\Cart;
use App\Models\Product;
use App\Services\Cart\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(private CartService $carts) {}

    public function index(Request $request): View
    {
        $items = Cart::with(['product.game', 'variant'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return view('frontend.cart.index', ['items' => $items]);
    }

    public function store(AddToCartRequest $request): RedirectResponse
    {
        $product = Product::findOrFail($request->input('product_id'));
        $variant = $request->filled('variant_id')
            ? $product->variants()->findOrFail($request->input('variant_id'))
            : null;

        $this->carts->add($request->user(), $product, $variant, (int) $request->input('qty', 1));

        return redirect()->route('cart.index');
    }

    public function update(Request $request, Cart $cart): RedirectResponse
    {
        abort_unless($cart->user_id === $request->user()->id, 403);

        $validated = $request->validate(['qty' => ['required', 'integer', 'min:1', 'max:999']]);

        $cart->update(['qty' => $validated['qty']]);

        return redirect()->route('cart.index');
    }

    public function destroy(Request $request, Cart $cart): RedirectResponse
    {
        abort_unless($cart->user_id === $request->user()->id, 403);

        // PRD 49.9: hapus 1 karakter = hapus seluruh bundle (anti-curang syarat pair).
        if ($cart->bundle_group_id) {
            Cart::where('bundle_group_id', $cart->bundle_group_id)
                ->where('user_id', $request->user()->id)
                ->delete();
        } else {
            $cart->delete();
        }

        return redirect()->route('cart.index');
    }
}
