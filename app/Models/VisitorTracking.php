<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitorTracking extends Model
{
    use HasFactory;

    // Nama tabel sesuai ER (bukan bentuk jamak default Eloquent).
    protected $table = 'visitor_tracking';

    protected $fillable = [
        'source',
        'medium',
        'landing_page',
        'user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
