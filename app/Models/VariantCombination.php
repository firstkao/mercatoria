<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VariantCombination extends Model
{
    protected $fillable = ['variant_id', 'attribute_key', 'attribute_value'];

    public function variant(): BelongsTo
    { return $this->belongsTo(ProductVariant::class, 'variant_id'); }
}
