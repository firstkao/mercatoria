<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug'])]
class Developer extends Model
{
    public function games(): HasMany
    {
        return $this->hasMany(Game::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}