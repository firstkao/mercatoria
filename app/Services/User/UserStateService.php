<?php

namespace App\Services\User;

use App\Models\Product;
use App\Models\ProductView;
use App\Models\User;

/**
 * State user (PRD 7.1/7.9) + kuota view (PRD 7.19) + countdown (PRD 7.10)
 * untuk blok dashboard Crush/Ex/ONS (Design Spec 10.1).
 */
class UserStateService
{
    public const VIEW_QUOTA = 10;

    /** State dengan kuota view terbatas (PRD 7.9: Crush/Ex/ONS = 10x view produk). */
    private const VIEW_LIMITED = ['crush', 'ex', 'ons'];

    public function stateName(User $user): string
    {
        if ($user->isAdmin()) {
            return 'admin';
        }

        if ($user->role === User::ROLE_CRUSH) {
            return 'crush';
        }

        return strtolower((string) ($user->customer?->current_state ?? 'MVP'));
    }

    public function isViewLimited(User $user): bool
    {
        return in_array($this->stateName($user), self::VIEW_LIMITED, true);
    }

    public function recordView(User $user, Product $product): void
    {
        if (! $this->isViewLimited($user)) {
            return;
        }

        // 1 produk = 1 hitungan walau dibuka berulang (PRD 7.19).
        ProductView::firstOrCreate(
            ['user_id' => $user->id, 'product_id' => $product->id],
            ['viewed_at' => now()]
        );
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
     * Sisa waktu sebelum auto-delete Crush (PRD 7.10) — "Sisa waktu: 24 Hari 6 Jam".
     */
    public function countdownLabel(User $user): string
    {
        $days = (int) setting('auto_delete_crush_days', 30);
        $deadline = $user->created_at->copy()->addDays($days);
        $remaining = (int) max(0, now()->diffInSeconds($deadline, false));

        $hari = intdiv($remaining, 86400);
        $jam = intdiv($remaining % 86400, 3600);

        return "Sisa waktu: {$hari} Hari {$jam} Jam";
    }
}
