<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuthLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogoutController extends Controller
{
    /**
     * Handle logout
     */
    public function destroy(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            AuthLog::create([
                'user_id' => $user->id,
                'email_attempt' => $user->email,
                'action' => 'logout',
                'reason' => null,
                'ip_address' => function_exists('get_real_ip') ? get_real_ip() : $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')
            ->with('success', 'Kamu berhasil keluar.');
    }
}
