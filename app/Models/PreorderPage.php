<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PreorderPage extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'content',
        'meta_description',
        'status',
    ];
}
