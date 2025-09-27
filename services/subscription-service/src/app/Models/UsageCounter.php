<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsageCounter extends Model
{
    protected $table = 'subscription.usage_counters';

    protected $fillable = [
        'subscription_id',
        'feature_key',
        'period_start',
        'period_end',
        'used_amount',
        'limit_value',
    ];

    protected $casts = [
        'period_start' => 'datetime',
        'period_end' => 'datetime',
        'used_amount' => 'int',
        'limit_value' => 'int',
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class, 'subscription_id');
    }
}
