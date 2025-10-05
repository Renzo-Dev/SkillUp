<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;



class RefreshToken extends Model
{
    protected $table = 'refresh_tokens';

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

    // Проверка валидности токена
    public function isValid(): bool
    {
        return $this->expires_at && $this->expires_at->isFuture();
    }

    // Статический метод для поиска валидного токена
    public static function findValidToken(string $refreshToken): ?self
    {
        return static::where('refresh_token', $refreshToken)
            ->where('expires_at', '>', now())
            ->first();
    }
}