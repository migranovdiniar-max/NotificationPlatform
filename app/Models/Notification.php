<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $fillable = [
        'event_id',
        'channel',
        'recipient',
        'status',
        'attempts',
        'last_error',
        'sent_at',
        'dedupe_key',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
