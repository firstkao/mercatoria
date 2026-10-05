<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'order_id',
    'from_status',
    'to_status',
    'changed_by',
    'admin_id',
    'note',
])]
class OrderStatusHistory extends Model
{
    /**
     * Nama tabel harus ditulis eksplisit. Migrasi membuat tabel
     * `order_status_history` (tunggal), sedangkan tanpa properti ini Laravel
     * menebak `order_status_histories` (jamak) — semua relasi
     * `Order::statusHistory()` lalu gagal dengan "table doesn't exist" dan
     * halaman detail pesanan (admin & pembeli) jadi 500.
     */
    protected $table = 'order_status_history';

    public const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }
}