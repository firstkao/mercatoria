<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AdminLog;
use App\Models\AuthLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LogController extends Controller
{
    public function admin(Request $request): View
    {
        return view('admin.logs.admin', [
            'logs' => AdminLog::with('admin')->orderByDesc('id')->paginate(30),
        ]);
    }

    public function auth(): View
    {
        return view('admin.logs.auth', [
            'logs' => AuthLog::orderByDesc('id')->paginate(30),
        ]);
    }

    public function activity(): View
    {
        return view('admin.logs.activity', [
            'logs' => ActivityLog::with('user')->orderByDesc('id')->paginate(30),
        ]);
    }
}
