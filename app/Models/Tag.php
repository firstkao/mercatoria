<?php

namespace App\Models;

use App\Models\Concerns\CachesNavList;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    use CachesNavList;
    use HasFactory;

    public const NAV_CACHE_KEY = 'nav_tags';

    protected $fillable = [
        'name',
        'slug',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }

    protected static function navQuery(): Builder
    {
        return static::orderBy('name');
    }
}
