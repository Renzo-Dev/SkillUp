<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;



class UserRefreshToken extends Model
{
    protected $table = 'user_refresh_tokens';

    protected $fillable = [
        'user_id',
        'refresh_token',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    // Связь с моделью User
    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
