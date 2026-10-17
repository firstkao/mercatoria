<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PreorderPage extends Model
{
    protected $fillable = [
        'title', 'slug', 'grapes_json', 'html_content',
        'status', 'meta_title', 'meta_description',
    ];

    public function isPublished(): bool
    { return $this->status === 'publish'; }

    public function scopePublished($query)
    { return $query->where('status', 'publish'); }
}
