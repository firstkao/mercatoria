<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialMedia extends Model
{
    protected $fillable = ['name', 'url', 'icon_url', 'icon_key', 'sort_order', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /** Kunci ikon bawaan yang punya SVG inline di layouts/icon-social.blade.php. */
    public const BUILT_IN_ICONS = ['instagram', 'facebook', 'x', 'threads', 'whatsapp', 'tiktok'];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
