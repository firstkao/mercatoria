<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\AddToCartRequest;
use App\Models\ActivityLog;
use App\Models\Cart;
use App\Models\Product;
use App\Models\UserNotification;
use App\Providers\AppServiceProvider;
use App\Services\Cart\CartService;
use App\Services\SplitBox\SplitBoxService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(private CartService $carts) {}

    public function index(Request $request): View
    {
        $items = Cart::with(['product.game.defaultStore', 'product.store', 'variant'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get()
            ->sortBy(fn (Cart $cart) => $cart->bundle_group_id ?? 'z'.$cart->id)
            ->values();

        // PRD 49.8: toast saat bundle Split Box di keranjang terjual ke pembeli lain.
        $purged = UserNotification::where('user_id', $request->user()->id)
            ->where('type', 'split_cart_purged')
            ->where('is_read', false);
        $purgedNotice = $purged->exists() ? SplitBoxService::CART_PURGED_MESSAGE : null;
        $purged->update(['is_read' => true]);

        return view('frontend.cart.index', ['items' => $items, 'purgedNotice' => $purgedNotice]);
    }

    public function store(AddToCartRequest $request): RedirectResponse|JsonResponse
    {
        $product = Product::findOrFail($request->input('product_id'));
        $variant = $request->filled('variant_id')
            ? $product->variants()->findOrFail($request->input('variant_id'))
            : null;

        $this->carts->add($request->user(), $product, $variant, (int) $request->input('qty', 1));

        ActivityLog::record('add_to_cart', 'Tambah ke keranjang');

        // AJAX dari halaman produk: balas JSON (angka keranjang di header ikut diperbarui tanpa reload).
        if ($request->expectsJson()) {
            [$total, $count] = AppServiceProvider::cartSummary($request->user());

            return response()->json([
                'ok' => true,
                'count' => $count,
                'total' => $total,
                'total_formatted' => format_rp($total),
            ]);
        }

        return redirect()->route('cart.index');
    }

    public function update(Request $request, Cart $cart): RedirectResponse
    {
        abort_unless($cart->user_id === $request->user()->id, 403);

        // PRD 49.9: qty item dalam bundle Split Box tidak bisa diubah satu per satu.
        if ($cart->bundle_group_id) {
            return redirect()->route('cart.index')->withErrors(['qty' => 'Jumlah item paket Split Box tidak dapat diubah. Hapus paket lalu pilih ulang.']);
        }

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

        ActivityLog::record('remove_from_cart', 'Hapus dari keranjang');

        return redirect()->route('cart.index');
    }
}
