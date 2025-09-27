<?php

namespace App\Models;

use App\Enums\SubscriptionSource;
use App\Enums\SubscriptionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    protected $table = 'subscription.subscriptions';

    protected $fillable = [
        'user_id',
        'plan_id',
        'status',
        'started_at',
        'expires_at',
        'trial_ends_at',
        'cancelled_at',
        'cancellation_reason',
        'auto_renew',
        'last_payment_id',
        'source',
    ];

    protected $casts = [
        'auto_renew' => 'bool',
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'status' => SubscriptionStatus::class,
        'source' => SubscriptionSource::class,
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(SubscriptionEvent::class, 'subscription_id');
    }

    public function usageCounters(): HasMany
    {
        return $this->hasMany(UsageCounter::class, 'subscription_id');
    }
}
