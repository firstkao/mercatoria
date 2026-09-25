<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'full_name',
    'whatsapp',
    'email',
    'city',
    'sales_channel',
    'store_link',
    'monthly_estimate',
    'notes',
])]
class ResellerApplication extends Model
{
    public const SALES_CHANNELS = [
        'instagram' => 'Instagram',
        'tiktok' => 'TikTok',
        'x' => 'X (Twitter)',
        'shopee' => 'Shopee',
        'tokopedia' => 'Tokopedia',
        'offline' => 'Toko offline / event',
        'lainnya' => 'Lainnya',
    ];

    public const MONTHLY_ESTIMATES = [
        '1-5' => '1–5 pcs',
        '6-20' => '6–20 pcs',
        '21-50' => '21–50 pcs',
        '50+' => 'Lebih dari 50 pcs',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}