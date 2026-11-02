<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function read(Request $request, AdminNotification $notification): RedirectResponse
    {
        $notification->update(['is_read' => true]);

        return $notification->target_url ? redirect($notification->target_url) : back();
    }

    /**
     * PRD 41.1: tombol "Tandai semua dibaca" pada bell admin.
     */
    public function readAll(Request $request): RedirectResponse
    {
        AdminNotification::where('is_read', false)->update(['is_read' => true]);

        return back()->with('status', 'Semua notifikasi admin ditandai dibaca.');
    }
}
