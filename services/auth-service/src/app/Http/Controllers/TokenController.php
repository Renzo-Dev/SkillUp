<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Contracts\JwtServiceInterface;
use App\Contracts\BlacklistServiceInterface;
use App\Contracts\RefreshTokenServiceInterface;
use App\DTOs\UserDTO;
use App\Http\Resources\MeResource;

class TokenController extends Controller
{
    public function __construct(
        protected JwtServiceInterface $jwtService,
        protected BlacklistServiceInterface $blacklistService,
        protected RefreshTokenServiceInterface $refreshTokenService
    ) {}

    /**
     * Обновление токенов
     */
    public function refresh(Request $request)
    {
        $refreshToken = $request->input('refresh_token');
        
        if (!$refreshToken) {
            return response()->json([
                'error' => 'Refresh token required',
                'message' => 'Refresh token is missing'
            ], 400);
        }

        $newTokens = $this->jwtService->refreshTokens($refreshToken);
        
        if ($newTokens) {
            return response()->json([
                'success' => true,
                'data' => [
                    'access_token' => $newTokens->accessToken,
                    'refresh_token' => $newTokens->refreshToken,
                ]
            ]);
        } else {
            return response()->json([
                'error' => 'Invalid refresh token',
                'message' => 'Refresh token is invalid or expired'
            ], 401);
        }
    }

    /**
     * Отзыв токена (отзывает все токены пользователя)
     */
    public function revoke(Request $request)
    {
        $token = $request->bearerToken();

        // Добавляем access токен в blacklist
        $blacklistResult = $this->blacklistService->addToken($token);
        
        // Получаем пользователя из токена для отзыва всех его refresh токенов
        $user = $this->jwtService->getUserFromToken($token);
        if ($user) {
            // Отзываем все refresh токены пользователя
            $this->refreshTokenService->revokeAllUserTokens($user);
        }
        
        if ($blacklistResult) {
            return response()->json([
                'success' => true,
                'message' => 'All tokens revoked successfully'
            ]);
        } else {
            return response()->json([
                'error' => 'Failed to revoke tokens',
                'message' => 'Tokens could not be revoked'
            ], 500);
        }
    }

    /**
     * Валидация токена
     */
    public function validate(Request $request)
    {
        $token = $request->bearerToken();

        $isValid = $this->jwtService->validateAccessToken($token);
        
        if ($isValid) {
            $user = $this->jwtService->getUserFromToken($token);
            $userDto = $user ? UserDTO::fromModel($user) : null;
            return (new MeResource($userDto))->additional([
                'valid' => true,
            ]);
        } else {
            return response()->json([
                'success' => true,
                'valid' => false,
                'message' => 'Token is invalid or expired'
            ]);
        }
    }
}
