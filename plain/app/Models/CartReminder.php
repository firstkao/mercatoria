<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'item_count',
    'reminder_count',
    'first_added_at',
    'sent_at',
])]
class CartReminder extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'item_count'     => 'integer',
            'reminder_count' => 'integer',
            'first_added_at' => 'datetime',
            'sent_at'        => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Reset timer — dipanggil saat user tambah/ubah item di cart.
     */
    public function resetTimer(int $itemCount): void
    {
        $this->update([
            'item_count'     => $itemCount,
            'first_added_at' => now(),
            'sent_at'        => null,
            'reminder_count' => 0,
        ]);
    }

    /**
     * Apakah reminder sudah saatnya dikirim?
     * (belum pernah dikirim, atau jeda terakhir > $minutes menit)
     */
    public function isDue(int $minutes = 30): bool
    {
        $cutoff = now()->subMinutes($minutes);

        // Belum pernah dikirim — cek dari first_added_at
        if ($this->sent_at === null) {
            return $this->first_added_at !== null
                && $this->first_added_at->lessThanOrEqualTo($cutoff);
        }

        // Sudah pernah dikirim — cek dari sent_at terakhir
        return $this->sent_at->lessThanOrEqualTo($cutoff);
    }
}
