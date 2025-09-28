<?php

namespace App\Http\Middleware;

use App\Services\Jwt\JwtVerifier;
use Closure;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JwtAuthenticate
{
    public function __construct(
        private JwtVerifier $verifier,
        private CacheRepository $cache,
    ) {
    }

    public function handle(Request $request, Closure $next, ?string $requiredScope = null): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => 'token_absent'], 401);
        }

        try {
            $parsed = $this->verifier->verify($token);
        } catch (\Throwable $throwable) {
            return response()->json(['error' => 'token_invalid'], 401);
        }

        $claims = $parsed->claims();
        if ($claims->has('exp') && $claims->get('exp')->getTimestamp() < now()->timestamp) {
            return response()->json(['error' => 'token_expired'], 401);
        }

        if ($claims->has('scope') && $requiredScope && !$this->hasScope($claims->get('scope'), $requiredScope)) {
            return response()->json(['error' => 'insufficient_scope'], 403);
        }

        if ($claims->has('jti')) {
            $cacheKey = sprintf('%s:%s', config('jwt.jti_cache_prefix'), $claims->get('jti'));
            if ($this->cache->has($cacheKey)) {
                return response()->json(['error' => 'token_revoked'], 401);
            }

            $this->cache->put($cacheKey, true, now()->addSeconds(config('jwt.jti_ttl')));
        }

        $request->attributes->set('jwt', $claims->all());

        return $next($request);
    }

    private function hasScope(string $scopeValue, string $requiredScope): bool
    {
        $scopes = array_filter(explode(' ', $scopeValue));

        return in_array($requiredScope, $scopes, true);
    }
}

