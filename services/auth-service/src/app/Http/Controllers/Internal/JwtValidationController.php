<?php

namespace App\Http\Controllers\Internal;

use App\Contracts\Services\JwtMetadataCacheServiceInterface;
use App\Contracts\Services\JwtServiceInterface;
use App\Contracts\Services\UserServiceInterface;
use App\Http\Resources\ApiErrorResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Facades\JWTAuth;

class JwtValidationController extends Controller
{
    public function __construct(
        private JwtServiceInterface $jwtService,
        private UserServiceInterface $userService,
        private JwtMetadataCacheServiceInterface $metadataCache,
    ) {}

    public function __invoke(Request $request): Response
    {
        try {
            $token = $this->extractToken($request);

            if ($token === null) {
                return $this->unauthorized('JWT токен не передан', 'TOKEN_MISSING');
            }

            $payload = $this->jwtService->decode($token);

            if (!$payload) {
                return $this->unauthorized('Пустой payload токена', 'PAYLOAD_EMPTY');
            }

            $userId = (int) ($payload['sub'] ?? 0);
            if ($userId <= 0) {
                return $this->unauthorized('Не найден идентификатор пользователя', 'USER_ID_MISSING');
            }

            $userModel = $this->userService->getModelById($userId);

            if ($userModel === null) {
                return $this->unauthorized('Пользователь не найден', 'USER_NOT_FOUND');
            }

            if (!$userModel->is_active) {
                return $this->forbidden('Пользователь деактивирован', 'USER_INACTIVE');
            }

            $metadata = $this->metadataCache->remember($userModel, $payload);

            return response()->noContent(204, [
                'X-User-Id' => (string) $metadata['user_id'],
                'X-Scopes' => implode(',', $metadata['scopes'] ?? []),
                'X-Subscription-Tier' => (string) ($metadata['subscription_tier'] ?? ''),
                'X-Email-Verified' => $metadata['email_verified'] ? 'true' : 'false',
            ]);
        } catch (TokenExpiredException $exception) {
            return $this->unauthorized('JWT токен истек', 'TOKEN_EXPIRED');
        } catch (TokenInvalidException $exception) {
            return $this->unauthorized('JWT токен недействителен', 'TOKEN_INVALID');
        } catch (TokenBlacklistedException $exception) {
            return $this->unauthorized('JWT токен отозван', 'TOKEN_BLACKLISTED');
        } catch (JWTException $exception) {
            return $this->unauthorized('Ошибка проверки JWT токена', 'JWT_ERROR');
        }
    }

    private function extractToken(Request $request): ?string
    {
        $authorization = $request->header('Authorization');

        if (!$authorization) {
            return null;
        }

        if (preg_match('/Bearer\s+(.*)$/i', $authorization, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function unauthorized(string $message, string $code): JsonResponse
    {
        return ApiErrorResource::create($message, 401, ['error_code' => $code])->response();
    }

    private function forbidden(string $message, string $code): JsonResponse
    {
        return ApiErrorResource::create($message, 403, ['error_code' => $code])->response();
    }
}

