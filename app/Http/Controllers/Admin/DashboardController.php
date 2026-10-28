<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $today = now()->startOfDay();
        $month = now()->startOfMonth();
        $week = now()->subWeek();

        return view('admin.dashboard', [
            'totalOrders' => Order::count(),
            'todayOrders' => Order::where('created_at', '>=', $today)->count(),
            'monthRevenue' => Payment::where('status', Payment::STATUS_VERIFIED)
                ->where('created_at', '>=', $month)
                ->sum('amount'),
            'newUsers' => User::where('created_at', '>=', $week)->count(),
            'pendingVerifications' => Payment::where('status', Payment::STATUS_PENDING)->count(),
            'newOrders' => Order::where('status', 'menunggu_pembayaran')->count(),
            'topProducts' => Product::orderByDesc('sold_count')->limit(5)->get(['id', 'name', 'sold_count']),
        ]);
    }
}
