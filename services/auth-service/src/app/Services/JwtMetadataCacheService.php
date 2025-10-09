<?php

namespace App\Services;

use App\Contracts\Services\JwtMetadataCacheServiceInterface;
use App\Models\User;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Token;

class JwtMetadataCacheService implements JwtMetadataCacheServiceInterface
{
    private CacheRepository $cache;

    public function __construct()
    {
        $store = config('jwt.cache_store', env('JWT_CACHE_STORE', 'redis'));
        $this->cache = Cache::store($store);
    }

    public function remember(User $user, array $payload, array $context = []): array
    {
        $jti = (string) ($payload['jti'] ?? null);

        if ($jti === '') {
            Log::warning('JwtMetadataCacheService: пропущен jti в payload');
            return [];
        }

        $ttl = $this->ttlFromPayload($payload);

        $data = [
            'user_id' => $user->id,
            'scopes' => $payload['scopes'] ?? $context['scopes'] ?? [],
            'subscription_tier' => $payload['subscription_tier'] ?? $context['subscription_tier'] ?? null,
            'email_verified' => $user->email_verified_at !== null,
            'expires_at' => now()->addSeconds($ttl)->toISOString(),
        ];

        $this->cache->put($this->key($jti), $data, $ttl);

        return $data;
    }

    public function rememberFromToken(string $token, User $user, array $context = []): array
    {
        $parsed = JWTAuth::manager()->decode(new Token($token));

        return $this->remember($user, $parsed->toArray(), $context);
    }

    public function get(string $jti): ?array
    {
        return $this->cache->get($this->key($jti));
    }

    public function forget(string $jti): void
    {
        $this->cache->forget($this->key($jti));
    }

    public function forgetByToken(string $token): void
    {
        try {
            $payload = JWTAuth::manager()->decode(new Token($token));
            $jti = (string) $payload->get('jti');

            if ($jti !== '') {
                $this->forget($jti);
            }
        } catch (\Throwable $exception) {
            Log::warning('JwtMetadataCacheService: не удалось удалить кеш по токену', [
                'message' => $exception->getMessage(),
            ]);
        }
    }

    private function key(string $jti): string
    {
        $prefix = config('jwt.cache_prefix', env('JWT_CACHE_PREFIX', 'auth:jwt'));

        return $prefix . ':' . $jti;
    }

    private function ttlFromPayload(array $payload): int
    {
        $exp = (int) ($payload['exp'] ?? 0);
        $ttl = max($exp - time(), config('jwt.cache_min_ttl', 5));

        $override = (int) env('JWT_CACHE_TTL', 0);

        if ($override > 0) {
            $ttl = min($ttl, $override);
        }

        return $ttl;
    }
}

