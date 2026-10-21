<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MerchCategory extends Model
{
    protected $fillable = [
        'name', 'slug', 'parent_id', 'description', 'icon_path', 'keyword_list', 'sort_order',
    ];

    protected $casts = ['keyword_list' => 'array'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(MerchCategory::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(MerchCategory::class, 'parent_id');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_merch_category');
    }
}
