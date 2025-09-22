<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefreshToken extends Model
{
  protected $fillable = [
    'user_id',
    'token_hash',
    'expires_at',
  ];

  protected $casts = [
    'expires_at' => 'datetime',
  ];

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  public function isExpired(): bool
  {
    return $this->expires_at->isPast();
  }
}
