<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailLog extends Model
{
    protected $fillable = [
        'template_id', 'recipient_email', 'subject',
        'status', 'error_message', 'sent_at',
    ];

    protected $casts = ['sent_at' => 'datetime'];

    public function template(): BelongsTo
    { return $this->belongsTo(EmailTemplate::class, 'template_id'); }

    public function isSent(): bool
    { return $this->status === 'sent'; }

    public function isFailed(): bool
    { return $this->status === 'failed'; }
}
