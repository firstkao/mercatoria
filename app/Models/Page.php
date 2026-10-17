<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = [
        'title', 'slug', 'content', 'featured_image_path',
        'status', 'meta_title', 'meta_description',
    ];

    public function isPublished(): bool
    { return $this->status === 'publish'; }

    public function isDraft(): bool
    { return $this->status === 'draft'; }

    public function scopePublished($query)
    { return $query->where('status', 'publish'); }
}
