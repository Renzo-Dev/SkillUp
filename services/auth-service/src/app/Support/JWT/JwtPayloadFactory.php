<?php

namespace App\Support\JWT;

use App\Models\User;
use Illuminate\Support\Str;

class JwtPayloadFactory
{
    /**
     * Создать payload для JWT токена
     * 
     * @param User $user
     * @return array
     */
    public function make(User $user): array
    {
        $now = time();
        $ttl = (int) config('jwt.ttl', 60); // минуты
        
        return [
            'iss' => config('app.url', 'http://localhost'), // Issuer
            'iat' => $now, // Issued At
            'exp' => $now + ($ttl * 60), // Expiration Time
            'nbf' => $now, // Not Before
            'sub' => (string) $user->getKey(), // Subject (User ID)
            'jti' => Str::uuid()->toString(), // JWT ID (уникальный идентификатор токена)
        ];
    }
    
    /**
     * Создать payload с кастомными данными
     * 
     * @param User $user
     * @param array $customClaims
     * @return array
     */
    public function makeWithCustomClaims(User $user, array $customClaims = []): array
    {
        $payload = $this->make($user);
        
        return array_merge($payload, $customClaims);
    }
}

