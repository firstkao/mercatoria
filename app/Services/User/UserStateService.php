<?php

namespace App\Services\User;

use App\Models\Product;
use App\Models\ProductView;
use App\Models\User;
use Illuminate\Database\QueryException;

/**
 * State user (PRD 7.1/7.9) + kuota view (PRD 7.19) + countdown (PRD 7.10)
 * untuk blok dashboard Crush/Ex/ONS (Design Spec 10.1).
 */
class UserStateService
{
    public const VIEW_QUOTA = 10;

    /** State dengan kuota view terbatas (PRD 7.9: Crush/Ex/ONS = 10x view produk). */
    private const VIEW_LIMITED = ['crush', 'ex', 'ons'];

    /**
     * Nama state user (lowercase, untuk logic).
     */
    public function stateName(User $user): string
    {
        if ($user->isAdmin()) {
            return 'admin';
        }

        if ($user->role === User::ROLE_CRUSH) {
            return 'crush';
        }

        return strtolower((string) ($user->customer?->current_state ?? 'mvp'));
    }

    /**
     * [FIX #1] Label untuk tampilan di blade — proper casing.
     */
    public function stateLabel(User $user): string
    {
        return strtoupper($this->stateName($user));
    }

    public function isViewLimited(User $user): bool
    {
        return in_array($this->stateName($user), self::VIEW_LIMITED, true);
    }

    /**
     * Record 1 view = 1 hitungan (PRD 7.19).
     * firstOrCreate: produk yang sama dibuka berulang TIDAK nambah hitungan.
     */
    public function recordView(User $user, Product $product): void
    {
        if (! $this->isViewLimited($user)) {
            return;
        }

        try {
            ProductView::firstOrCreate(
                ['user_id' => $user->id, 'product_id' => $product->id],
                ['viewed_at' => now()]
            );
        } catch (QueryException) {
            // [FIX #2] Race condition: 2 request barengan — unique constraint.
            // Sudah tercatat, abaikan.
        }
    }

    /**
     * @return array{used: int, total: int}
     */
    public function viewUsage(User $user): array
    {
        return [
            'used' => ProductView::where('user_id', $user->id)->count(),
            'total' => self::VIEW_QUOTA,
        ];
    }

    /**
     * Sisa view tersisa.
     */
    public function remainingViews(User $user): int
    {
        return max(0, self::VIEW_QUOTA - $this->viewUsage($user)['used']);
    }

    /**
     * Sisa waktu sebelum auto-delete Crush (PRD 7.10: 30 hari SETELAH VERIFIKASI)
     * — "Sisa waktu: 24 Hari 6 Jam".
     */
    public function countdownLabel(User $user): string
    {
        if ($user->email_verified_at === null) {
            return 'Belum verifikasi';
        }

        $days = (int) setting('auto_delete_crush_days', 30);
        $deadline = $user->email_verified_at->copy()->addDays($days);
        $remaining = (int) max(0, now()->diffInSeconds($deadline, false));

        $hari = intdiv($remaining, 86400);
        $jam = intdiv($remaining % 86400, 3600);

        return "Sisa waktu: {$hari} Hari {$jam} Jam";
    }
}