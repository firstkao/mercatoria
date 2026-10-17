<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AdminLog;
use App\Models\AuthLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditLogController extends Controller
{
    public function activity(Request $request): View
    {
        $filters = $this->filters($request);
        $logs = ActivityLog::query()->with('user')
            ->when($filters['q'], fn (Builder $query, string $term) => $query->where(function (Builder $query) use ($term): void {
                $query->where('action', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%")
                    ->orWhere('url', 'like', "%{$term}%")
                    ->orWhere('ip_address', 'like', "%{$term}%")
                    ->orWhereHas('user', fn (Builder $user) => $user->where('name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%"));
            }))
            ->when($filters['action'], fn (Builder $query, string $action) => $query->where('action', $action));
        $this->applyDateFilters($logs, $filters);

        return view('admin.logs.index', [
            'kind' => 'activity',
            'title' => 'Activity Log',
            'logs' => $logs->latest('id')->paginate(50)->withQueryString(),
            'filters' => $filters,
        ]);
    }

    public function auth(Request $request): View
    {
        $filters = $this->filters($request);
        $logs = AuthLog::query()->with('user')
            ->when($filters['q'], fn (Builder $query, string $term) => $query->where(function (Builder $query) use ($term): void {
                $query->where('email_attempt', 'like', "%{$term}%")
                    ->orWhere('action', 'like', "%{$term}%")
                    ->orWhere('reason', 'like', "%{$term}%")
                    ->orWhere('ip_address', 'like', "%{$term}%");
            }))
            ->when($filters['action'], fn (Builder $query, string $action) => $query->where('action', $action));
        $this->applyDateFilters($logs, $filters);

        return view('admin.logs.index', [
            'kind' => 'auth',
            'title' => 'Auth Log',
            'logs' => $logs->latest('id')->paginate(50)->withQueryString(),
            'filters' => $filters,
        ]);
    }

    public function admin(Request $request): View
    {
        $filters = $this->filters($request);
        $logs = AdminLog::query()->with('admin')
            ->when($filters['q'], fn (Builder $query, string $term) => $query->where(function (Builder $query) use ($term): void {
                $query->where('action', 'like', "%{$term}%")
                    ->orWhere('model', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%")
                    ->orWhere('ip_address', 'like', "%{$term}%")
                    ->orWhereHas('admin', fn (Builder $admin) => $admin->where('name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%"));
            }))
            ->when($filters['action'], fn (Builder $query, string $action) => $query->where('action', $action));
        $this->applyDateFilters($logs, $filters);

        return view('admin.logs.index', [
            'kind' => 'admin',
            'title' => 'Admin Log',
            'logs' => $logs->latest('id')->paginate(50)->withQueryString(),
            'filters' => $filters,
        ]);
    }

    public function export(Request $request, string $kind): StreamedResponse
    {
        abort_unless(in_array($kind, ['activity', 'auth', 'admin'], true), 404);
        $filters = $this->filters($request);
        $query = match ($kind) {
            'activity' => ActivityLog::query()->with('user'),
            'auth' => AuthLog::query()->with('user'),
            'admin' => AdminLog::query()->with('admin'),
        };
        $this->applySearch($query, $kind, $filters['q']);
        if ($filters['action']) {
            $query->where('action', $filters['action']);
        }
        $this->applyDateFilters($query, $filters);

        return response()->streamDownload(function () use ($query, $kind): void {
            $output = fopen('php://output', 'w');
            if ($output === false) {
                throw new \RuntimeException('Unable to open CSV output stream.');
            }
            fputcsv($output, ['ID', 'Waktu', 'User/Admin', 'Action', 'Detail', 'IP']);
            foreach ($query->latest('id')->cursor() as $log) {
                $person = match ($kind) {
                    'activity', 'auth' => $log->user?->email ?? $log->email_attempt ?? '',
                    'admin' => $log->admin?->email ?? '',
                };
                $detail = match ($kind) {
                    'activity' => $log->description ?? $log->url ?? '',
                    'auth' => $log->reason ?? '',
                    'admin' => json_encode([
                        'description' => $log->description,
                        'model' => $log->model,
                        'model_id' => $log->model_id,
                        'old' => $log->old_value,
                        'new' => $log->new_value,
                    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                };
                fputcsv($output, array_map($this->csvCell(...), [
                    $log->id,
                    $log->created_at,
                    $person,
                    $log->action,
                    $detail,
                    $log->ip_address,
                ]));
            }
            fclose($output);
        }, "{$kind}-logs-".now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function filters(Request $request): array
    {
        return $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'action' => ['nullable', 'string', 'max:64'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]) + ['q' => null, 'action' => null, 'from' => null, 'to' => null];
    }

    private function applyDateFilters(Builder $query, array $filters): void
    {
        $query->when($filters['from'] ?? null, fn (Builder $query, string $from) => $query->whereDate('created_at', '>=', $from))
            ->when($filters['to'] ?? null, fn (Builder $query, string $to) => $query->whereDate('created_at', '<=', $to));
    }

    private function applySearch(Builder $query, string $kind, ?string $term): void
    {
        if (! $term) {
            return;
        }

        $query->where(function (Builder $query) use ($kind, $term): void {
            $query->where('action', 'like', "%{$term}%")
                ->orWhere('ip_address', 'like', "%{$term}%");
            if ($kind === 'activity') {
                $query->orWhere('description', 'like', "%{$term}%")
                    ->orWhere('url', 'like', "%{$term}%")
                    ->orWhereHas('user', fn (Builder $user) => $user->where('name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%"));
            } elseif ($kind === 'auth') {
                $query->orWhere('email_attempt', 'like', "%{$term}%")
                    ->orWhere('reason', 'like', "%{$term}%");
            } else {
                $query->orWhere('model', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%")
                    ->orWhereHas('admin', fn (Builder $admin) => $admin->where('name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%"));
            }
        });
    }

    private function csvCell(mixed $value): string
    {
        $value = (string) $value;

        return preg_match('/^\s*[=+\-@]/', $value) ? "'".$value : $value;
    }
}
