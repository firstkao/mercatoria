<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

class BestSellerController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));
        $filter = $request->query('filter'); // ranked | all | excluded

        $query = Product::query()
            ->with(['images', 'game'])
            ->when($search !== '', fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->when($filter === 'ranked', fn ($q) => $q->whereNotNull('best_seller_rank'))
            ->when($filter === 'excluded', fn ($q) => $q->where('exclude_best_seller', true))
            ->orderByRaw('CASE WHEN best_seller_rank IS NULL THEN 1 ELSE 0 END')
            ->orderBy('best_seller_rank')
            ->orderByDesc('best_seller_score');

        $products = $query->paginate(40)->withQueryString();

        $stats = [
            'total_ranked' => Product::whereNotNull('best_seller_rank')->count(),
            'total_sold' => Product::sum('best_seller_score'),
            'period_days' => Setting::integer('best_seller_period_days', 365),
            'limit' => Setting::integer('best_seller_limit', 8),
            'min_sales' => Setting::integer('best_seller_min_sales', 1),
            'last_updated' => Product::query()->max('updated_at'),
        ];

        return view('admin.best-sellers.index', [
            'products' => $products,
            'stats' => $stats,
            'search' => $search,
            'filter' => $filter,
        ]);
    }

    public function refresh(): RedirectResponse
    {
        Artisan::call('products:refresh-best-sellers');
        $output = trim(Artisan::output());

        AdminLog::record('refresh_best_sellers');

        return back()->with('status', 'Ranking diperbarui. ' . $output);
    }

    public function toggleExclude(Product $product): RedirectResponse
    {
        $product->update(['exclude_best_seller' => ! $product->exclude_best_seller]);

        AdminLog::record('toggle_exclude_best_seller', $product, [
            'name' => $product->name,
            'excluded' => $product->exclude_best_seller,
        ]);

        return back()->with('status', $product->exclude_best_seller
            ? "\"{$product->name}\" dikecualikan dari best seller."
            : "\"{$product->name}\" kembali diikutkan best seller.");
    }
}