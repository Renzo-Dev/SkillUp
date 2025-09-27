<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookLog extends Model
{
    protected $table = 'subscription.webhook_logs';

    protected $fillable = [
        'direction',
        'endpoint',
        'request_body',
        'response_body',
        'status_code',
        'processed_at',
    ];

    protected $casts = [
        'request_body' => 'array',
        'response_body' => 'array',
        'processed_at' => 'datetime',
        'status_code' => 'int',
    ];

    public $incrementing = false;
    protected $keyType = 'string';
}
