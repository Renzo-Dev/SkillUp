<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionEvent extends Model
{
    protected $table = 'subscription.subscription_events';

    protected $fillable = [
        'subscription_id',
        'event_type',
        'payload',
        'processed_at',
        'correlation_id',
        'event_id',
    ];

    protected $casts = [
        'payload' => 'array',
        'processed_at' => 'datetime',
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class, 'subscription_id');
    }
}
