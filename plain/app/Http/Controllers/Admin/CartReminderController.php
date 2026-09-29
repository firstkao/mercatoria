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
    public function index(Request $request): View
    {
        $reminders = CartReminder::query()
            ->with('user')
            ->latest('sent_at')
            ->paginate(40);

        $stats = [
            'total_sent' => CartReminder::count(),
            'sent_30d' => CartReminder::where('sent_at', '>=', now()->subDays(30))->count(),
            'active_carts' => DB::table('cart_items')->distinct()->count('user_id'),
            'reminder1' => CartReminder::where('reminder_number', 1)->count(),
            'reminder2' => CartReminder::where('reminder_number', 2)->count(),
        ];

        return view('admin.cart-reminders.index', [
            'reminders' => $reminders,
            'stats' => $stats,
        ]);
    }

    public function refresh(): RedirectResponse
    {
        Artisan::call('carts:send-reminders');
        $output = trim(Artisan::output());

        return back()->with('status', 'Reminder dijalankan. ' . $output);
    }
}