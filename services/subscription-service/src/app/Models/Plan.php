<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Plan extends Model
{
    protected $table = 'subscription.plans';

    protected $fillable = [
        'code',
        'name',
        'description',
        'price_cents',
        'currency',
        'billing_cycle',
        'trial_period_days',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'bool',
        'trial_period_days' => 'int',
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    public function features(): HasMany
    {
        return $this->hasMany(PlanFeature::class, 'plan_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'plan_id');
    }
}
