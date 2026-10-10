<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CartReminder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CartReminderController extends Controller
{
    /**
     * Halaman daftar cart reminder + statistik.
     */
    public function index(Request $request): View
    {
        $reminders = CartReminder::query()
            ->with('user')
            ->latest('sent_at')
            ->paginate(40);

        $totalSent   = CartReminder::count();
        $uniqueUsers = CartReminder::distinct()->count('user_id');

        $stats = [
            'active_carts' => DB::table('cart_items')->distinct()->count('user_id'),
            'total_sent'   => $totalSent,
            'sent_30d'     => CartReminder::where('sent_at', '>=', now()->subDays(30))->count(),
            'avg_per_cart' => $uniqueUsers > 0
                ? round($totalSent / $uniqueUsers, 1)
                : 0,
        ];

        return view('admin.cart-reminders.index', [
            'reminders' => $reminders,
            'stats'     => $stats,
        ]);
    }

    /**
     * Jalankan command reminder manual (dari tombol "Jalankan Sekarang").
     */
    public function refresh(): RedirectResponse
    {
        Artisan::call('carts:send-reminders', ['--minutes' => 30]);
        $output = trim(Artisan::output());

        return back()->with('status', 'Reminder dijalankan. ' . $output);
    }
}
