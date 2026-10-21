<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Developer extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'logo_path', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function games(): BelongsToMany
    {
        return $this->belongsToMany(Game::class, 'game_developer');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
