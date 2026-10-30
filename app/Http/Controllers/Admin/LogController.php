<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AdminLog;
use App\Models\AuthLog;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LogController extends Controller
{
    // ── Filter shared: terapkan search/action/date ke query ──
    private function applyFilters($query, Request $request)
    {
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('action', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }

        if ($request->filled('date')) {
            $date = match ($request->input('date')) {
                'today' => now()->startOfDay(),
                'week'  => now()->subDays(7),
                'month' => now()->subDays(30),
                default => null,
            };
            if ($date) {
                $query->where('created_at', '>=', $date);
            }
        }

        return $query;
    }

    // ── Admin Log (aksi admin: create/update/delete model) ──
    public function admin(Request $request): View
    {
        $query = AdminLog::with('admin')->orderByDesc('id');
        $this->applyFilters($query, $request);

        return view('admin.logs.admin', [
            'logs' => $query->paginate(30)->withQueryString(),
        ]);
    }

    // ── Auth Log (login/logout/gagal) ──
    public function auth(Request $request): View
    {
        $query = AuthLog::orderByDesc('id');
        $this->applyFilters($query, $request);

        return view('admin.logs.auth', [
            'logs' => $query->paginate(30)->withQueryString(),
        ]);
    }

    // ── Activity Log (aksi user: browse, cart, checkout, dll) ──
    public function activity(Request $request): View
    {
        $query = ActivityLog::with('user')->orderByDesc('id');
        $this->applyFilters($query, $request);

        return view('admin.logs.activity', [
            'logs' => $query->paginate(30)->withQueryString(),
        ]);
    }

    // ── Export CSV (semua tab) ──
    public function export(Request $request): StreamedResponse
    {
        $tab = $request->query('tab', 'admin-logs');

        $query = match ($tab) {
            'activity-logs' => ActivityLog::with('user')->orderByDesc('id'),
            'auth-logs'     => AuthLog::orderByDesc('id'),
            default         => AdminLog::with('admin')->orderByDesc('id'),
        };

        $this->applyFilters($query, $request);

        $filename = $tab.'-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($query, $tab) {
            $out = fopen('php://output', 'w');

            if ($tab === 'admin-logs') {
                fputcsv($out, ['Waktu', 'Admin', 'Aksi', 'Model', 'ID', 'Deskripsi', 'Old', 'New']);
                $query->chunk(500, function ($logs) use ($out) {
                    foreach ($logs as $log) {
                        fputcsv($out, [
                            $log->created_at->format('Y-m-d H:i:s'),
                            $log->admin->name ?? '-',
                            $log->action,
                            $log->model ?? '-',
                            $log->model_id ?? '-',
                            $log->description ?? '-',
                            is_array($log->old_value) ? json_encode($log->old_value, JSON_UNESCAPED_UNICODE) : ($log->old_value ?? '-'),
                            is_array($log->new_value) ? json_encode($log->new_value, JSON_UNESCAPED_UNICODE) : ($log->new_value ?? '-'),
                        ]);
                    }
                });
            } elseif ($tab === 'auth-logs') {
                fputcsv($out, ['Waktu', 'Aksi', 'IP', 'Deskripsi']);
                $query->chunk(500, function ($logs) use ($out) {
                    foreach ($logs as $log) {
                        fputcsv($out, [
                            $log->created_at->format('Y-m-d H:i:s'),
                            $log->action,
                            $log->ip_address ?? '-',
                            $log->description ?? '-',
                        ]);
                    }
                });
            } else {
                fputcsv($out, ['Waktu', 'User', 'Aksi', 'IP', 'URL', 'Deskripsi']);
                $query->chunk(500, function ($logs) use ($out) {
                    foreach ($logs as $log) {
                        fputcsv($out, [
                            $log->created_at->format('Y-m-d H:i:s'),
                            $log->user->name ?? '-',
                            $log->action,
                            $log->ip_address ?? '-',
                            $log->url ?? '-',
                            $log->description ?? '-',
                        ]);
                    }
                });
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}