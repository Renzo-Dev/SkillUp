<?php

namespace App\Support;

use Illuminate\Http\Request;

class AuthContext
{
    // Получаем user_id из JWT
    public static function userId(Request $request): ?string
    {
        $claims = $request->attributes->get('jwt', []);

        return $claims['sub'] ?? null;
    }
}

