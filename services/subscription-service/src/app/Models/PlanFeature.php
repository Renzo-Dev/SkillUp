<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanFeature extends Model
{
    protected $table = 'subscription.plan_features';

    protected $fillable = [
        'plan_id',
        'feature_key',
        'limit_value',
        'metadata',
    ];

    protected $casts = [
        'limit_value' => 'int',
        'metadata' => 'array',
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }
}
