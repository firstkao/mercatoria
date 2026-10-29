<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * PRD 22.5/41.2: search admin — produk (nama/SKU), order (no order/email), user (nama/email/HP).
     * Hasil terkelompok per tipe, tiap item membawa link ke halaman terkait.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        if ($q === '' || mb_strlen($q) > 100) {
            return response()->json(['products' => [], 'orders' => [], 'users' => []]);
        }

        $like = '%'.$q.'%';

        return response()->json([
            'products' => Product::where(function ($query) use ($like) {
                $query->where('name', 'like', $like)->orWhere('sku', 'like', $like);
            })->orderByDesc('id')->limit(5)->get(['id', 'name', 'sku'])
                ->map(fn (Product $product) => [
                    'title' => $product->name,
                    'meta' => $product->sku,
                    'url' => route('office.products.edit', $product),
                ])->values(),

            'orders' => Order::with('user:id,name,email')
                ->where(function ($query) use ($like) {
                    $query->where('no_order', 'like', $like)
                        ->orWhereHas('user', fn ($u) => $u->where('email', 'like', $like));
                })
                ->orderByDesc('id')->limit(5)->get(['id', 'no_order', 'user_id', 'status'])
                ->map(fn (Order $order) => [
                    'title' => $order->no_order,
                    'meta' => $order->user?->email,
                    'url' => route('office.orders.show', $order),
                ])->values(),

            'users' => User::where(function ($query) use ($like) {
                $query->where('name', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('no_hp', 'like', $like);
            })->orderByDesc('id')->limit(5)->get(['id', 'name', 'email', 'no_hp'])
                ->map(fn (User $user) => [
                    'title' => $user->name,
                    'meta' => $user->email.' · '.$user->no_hp,
                    'url' => route('office.users.edit', $user),
                ])->values(),
        ]);
    }
}
