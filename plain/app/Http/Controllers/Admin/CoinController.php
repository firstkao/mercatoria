<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\CoinLot;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CoinController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        // Hanya tampilkan user yang punya minimal 1 baris di coin_lots
        $users = User::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('full_name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('whatsapp', 'like', "%{$q}%");
                });
            })
            ->whereExists(function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('coin_lots')
                    ->whereColumn('coin_lots.user_id', 'users.id');
            })
            ->orderBy('full_name')
            ->paginate(30)
            ->withQueryString();

        // Ringkasan per user
        $summaries = [];
        foreach ($users as $u) {
            $summaries[$u->id] = $this->summarizeUser($u->id);
        }

        return view('admin.coins.index', compact('users', 'q', 'summaries'));
    }

    public function show(User $user): View
    {
        $summary = $this->summarizeUser($user->id);

        $lots = CoinLot::where('user_id', $user->id)
            ->orderByRaw('expires_at IS NULL ASC, expires_at ASC')
            ->orderByDesc('earned_at')
            ->paginate(30);

        return view('admin.coins.show', compact('user', 'summary', 'lots'));
    }

    public function store(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'action'         => ['required', 'in:add,subtract,reset_expiry'],
            'amount'         => ['required_if:action,add,subtract', 'nullable', 'integer', 'min:1', 'max:10000000'],
            'expires_months' => ['nullable', 'integer', 'min:1', 'max:120'],
            'reason'         => ['required', 'string', 'max:500'],
        ], [
            'amount.required_if' => 'Jumlah koin wajib diisi untuk aksi ini.',
            'reason.required'    => 'Alasan wajib diisi untuk audit trail.',
        ]);

        $reason = trim($validated['reason']);
        $action = $validated['action'];

        DB::transaction(function () use ($validated, $user, $action, $reason) {
            if ($action === 'add') {
                $this->addCoins(
                    $user,
                    (int) $validated['amount'],
                    (int) ($validated['expires_months'] ?? 12),
                );
            } elseif ($action === 'subtract') {
                $this->subtractCoins($user, (int) $validated['amount']);
            } elseif ($action === 'reset_expiry') {
                $this->resetExpiry(
                    $user,
                    (int) ($validated['expires_months'] ?? 12),
                );
            }
        });

        AdminLog::record('coin_adjustment', $user, [
            'action' => $action,
            'amount' => $validated['amount'] ?? null,
            'expires_months' => $validated['expires_months'] ?? null,
            'reason' => $reason,
        ]);

        return back()->with('status', 'Penyesuaian koin berhasil diterapkan.');
    }

    public function destroyLot(CoinLot $lot): RedirectResponse
    {
        $meta = [
            'user_id'   => $lot->user_id,
            'source'    => $lot->source,
            'amount'    => $lot->amount,
            'remaining' => $lot->remaining,
        ];

        $lot->delete();

        AdminLog::record('coin_lot_deleted', null, $meta);

        return back()->with('status', 'Lot koin dihapus.');
    }

    // ============================================================
    // HELPERS
    // ============================================================

    /**
     * Ringkasan koin satu user.
     */
    private function summarizeUser(int $userId): array
    {
        $now           = now();
        $warnThreshold = $now->copy()->addDays(30);

        $base = fn () => CoinLot::where('user_id', $userId);

        return [
            'active' => (int) $base()
                ->where('remaining', '>', 0)
                ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', $now))
                ->sum('remaining'),

            'expiring_soon' => (int) $base()
                ->where('remaining', '>', 0)
                ->where('expires_at', '>', $now)
                ->where('expires_at', '<=', $warnThreshold)
                ->sum('remaining'),

            'expired' => (int) $base()
                ->where('remaining', '>', 0)
                ->where('expires_at', '<=', $now)
                ->sum('remaining'),

            'total_earned' => (int) $base()->sum('amount'),

            'total_spent' => (int) ($base()->sum('amount') - $base()->sum('remaining')),
        ];
    }

    private function addCoins(User $user, int $amount, int $expiresMonths): void
    {
        $earnedAt = now();
        $expiry   = $earnedAt->copy()->addMonths($expiresMonths);

        CoinLot::create([
            'user_id'    => $user->id,
            'source'     => 'adjustment',
            'order_id'   => null,
            'amount'     => $amount,
            'remaining'  => $amount,
            'earned_at'  => $earnedAt,
            'expires_at' => $expiry,
        ]);
    }

    private function subtractCoins(User $user, int $amount): void
    {
        // FIFO: kurangi dari lot yang paling cepat expired dulu
        $lots = CoinLot::where('user_id', $user->id)
            ->where('remaining', '>', 0)
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->orderByRaw('expires_at IS NULL ASC, expires_at ASC')
            ->lockForUpdate()
            ->get();

        $available = (int) $lots->sum('remaining');

        if ($available < $amount) {
            throw ValidationException::withMessages([
                'amount' => 'Saldo koin aktif user cuma ' . number_format($available, 0, ',', '.')
                    . '. Tidak cukup untuk dikurangi ' . number_format($amount, 0, ',', '.') . '.',
            ]);
        }

        $needed = $amount;
        foreach ($lots as $lot) {
            if ($needed <= 0) break;
            $take = min($lot->remaining, $needed);
            $lot->decrement('remaining', $take);
            $needed -= $take;
        }
    }

    private function resetExpiry(User $user, int $expiresMonths): void
    {
        $newExpiry = now()->addMonths($expiresMonths);

        // Reset semua lot yang masih aktif (sisa > 0 & belum expired)
        CoinLot::where('user_id', $user->id)
            ->where('remaining', '>', 0)
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->update(['expires_at' => $newExpiry]);
    }
}
