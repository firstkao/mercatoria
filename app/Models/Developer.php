<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Developer extends Model
{
    use HasFactory;

    public const NAV_CACHE_KEY = 'nav_developers_active';

    protected static function booted(): void
    {
        $flush = fn () => Cache::forget(self::NAV_CACHE_KEY);
        static::saved($flush);
        static::deleted($flush);
    }

    protected $fillable = [
        'name',
        'slug',
        'logo_path',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function games(): BelongsToMany
    {
        return $this->belongsToMany(Game::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
