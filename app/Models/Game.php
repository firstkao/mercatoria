<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Game extends Model
{
    protected $fillable = [
        'name', 'slug', 'default_store_id', 'description', 'logo_path', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function defaultStore(): BelongsTo
    { return $this->belongsTo(Store::class, 'default_store_id'); }

    public function developers(): BelongsToMany
    { return $this->belongsToMany(Developer::class, 'game_developer'); }

    public function products(): BelongsToMany
    { return $this->belongsToMany(Product::class, 'product_game'); }

    public function scopeActive($query)
    { return $query->where('is_active', true); }
}
