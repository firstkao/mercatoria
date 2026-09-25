<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AdminLog;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $isAdminTab = $request->query('tab') === 'admin';
        $labels = $isAdminTab ? AdminLog::LABELS : ActivityLog::LABELS;
        $action = array_key_exists((string) $request->query('aksi'), $labels) ? $request->query('aksi') : null;
        $search = trim((string) $request->query('q'));
        $from = $this->date($request->query('dari'));
        $until = $this->date($request->query('sampai'));

        $query = $isAdminTab
            ? AdminLog::query()->with('admin')
                ->when($search !== '', fn ($q) => $q->whereHas('admin', fn ($admin) => $admin->where('name', 'like', "%{$search}%")))
            : ActivityLog::query()->with('user')
                ->when($search !== '', fn ($q) => $q->where(fn ($inner) => $inner
                    ->where('ip_address', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($user) => $user->where('full_name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))));

        $logs = $query
            ->when($action, fn ($q) => $q->where('action', $action))
            ->when($from, fn ($q) => $q->where('created_at', '>=', $from->copy()->startOfDay()->utc()))
            ->when($until, fn ($q) => $q->where('created_at', '<=', $until->copy()->endOfDay()->utc()))
            ->latest('id')
            ->paginate(50)
            ->withQueryString();

        return view('admin.logs.index', [
            'logs' => $logs,
            'isAdminTab' => $isAdminTab,
            'labels' => $labels,
            'filters' => ['aksi' => $action, 'q' => $search, 'dari' => $from?->toDateString(), 'sampai' => $until?->toDateString()],
        ]);
    }

    private function date(mixed $value): ?Carbon
    {
        if (! is_string($value) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }

        return Carbon::createFromFormat('Y-m-d', $value, 'Asia/Jakarta');
    }
}