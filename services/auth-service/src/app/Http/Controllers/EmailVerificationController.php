<?php

namespace App\Http\Controllers;

use App\Contracts\Services\EmailVerificationServiceInterface;
use App\Contracts\Services\CustomLoggerInterface;
use App\Contracts\Services\UserServiceInterface;
use App\Http\Resources\ApiErrorResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Контроллер для верификации email
 */
class EmailVerificationController extends Controller
{
    public function __construct(
        private EmailVerificationServiceInterface $emailVerificationService,
        private CustomLoggerInterface $logger,
        private UserServiceInterface $userService
    ) {}

    /**
     * Подтверждение email по токену
     */
    public function verifyEmail(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'token' => 'required|string|size:64'
            ]);

            if ($validator->fails()) {
                return ApiErrorResource::create(
                    'Неверный формат токена',
                    400
                )->response();
            }

            $token = $request->input('token');
            
            // Проверяем токен
            $verification = $this->emailVerificationService->verifyToken($token);
            
            if (!$verification) {
                return ApiErrorResource::create(
                    'Токен недействителен или истек',
                    400
                )->response();
            }

            // Подтверждаем email
            $confirmed = $this->emailVerificationService->confirmEmail($token);
            
            if (!$confirmed) {
                return ApiErrorResource::create(
                    'Ошибка подтверждения email',
                    500
                )->response();
            }

            $this->logger->controllerInfo("Email подтвержден для пользователя {$verification->user_id}");

            return response()->json([
                'success' => true,
                'message' => 'Email успешно подтвержден',
                'data' => [
                    'user_id' => $verification->user_id,
                    'email' => $verification->email,
                    'verified_at' => $verification->fresh()->verified_at
                ]
            ], 200);

        } catch (\Exception $e) {
            $this->logger->controllerError("Ошибка верификации email: " . $e->getMessage());
            
            return ApiErrorResource::create(
                'Ошибка подтверждения email: ' . $e->getMessage(),
                500
            )->response();
        }
    }

    /**
     * Повторная отправка токена верификации
     */
    public function resendVerification(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email'
            ]);

            if ($validator->fails()) {
                return ApiErrorResource::create(
                    'Неверный формат email',
                    400
                )->response();
            }

            $email = $request->input('email');
            
            // Находим пользователя
            $userDTO = $this->userService->findUserByEmail($email);
            
            if (!$userDTO) {
                return ApiErrorResource::create(
                    'Пользователь не найден',
                    404
                )->response();
            }

            // Получаем модель пользователя
            $user = User::find($userDTO->id);
            
            if (!$user) {
                return ApiErrorResource::create(
                    'Пользователь не найден',
                    404
                )->response();
            }

            // Проверяем, не верифицирован ли уже email
            if ($this->emailVerificationService->isEmailVerified($user, $email)) {
                return ApiErrorResource::create(
                    'Email уже подтвержден',
                    400
                )->response();
            }

            // Создаем новый токен
            $token = $this->emailVerificationService->createVerificationToken($user, $email);
            
            if (!$token) {
                return ApiErrorResource::create(
                    'Ошибка создания токена верификации',
                    500
                )->response();
            }

            $this->logger->controllerInfo("Создан новый токен верификации для пользователя {$user->id}");

            return response()->json([
                'success' => true,
                'message' => 'Токен верификации создан. Проверьте email для подтверждения.',
                'data' => [
                    'user_id' => $user->id,
                    'email' => $email,
                    'expires_at' => $token->expires_at
                ]
            ], 200);

        } catch (\Exception $e) {
            $this->logger->controllerError("Ошибка создания токена верификации: " . $e->getMessage());
            
            return ApiErrorResource::create(
                'Ошибка создания токена: ' . $e->getMessage(),
                500
            )->response();
        }
    }

    /**
     * Проверка статуса верификации email
     */
    public function checkVerificationStatus(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email'
            ]);

            if ($validator->fails()) {
                return ApiErrorResource::create(
                    'Неверный формат email',
                    400
                )->response();
            }

            $email = $request->input('email');
            
            // Находим пользователя
            $userDTO = $this->userService->findUserByEmail($email);
            
            if (!$userDTO) {
                return ApiErrorResource::create(
                    'Пользователь не найден',
                    404
                )->response();
            }

            $user = User::find($userDTO->id);
            
            if (!$user) {
                return ApiErrorResource::create(
                    'Пользователь не найден',
                    404
                )->response();
            }

            // Проверяем статус верификации
            $isVerified = $this->emailVerificationService->isEmailVerified($user, $email);
            $activeToken = $this->emailVerificationService->getActiveToken($user, $email);

            return response()->json([
                'success' => true,
                'data' => [
                    'email' => $email,
                    'is_verified' => $isVerified,
                    'has_active_token' => $activeToken !== null,
                    'token_expires_at' => $activeToken?->expires_at
                ]
            ], 200);

        } catch (\Exception $e) {
            $this->logger->controllerError("Ошибка проверки статуса верификации: " . $e->getMessage());
            
            return ApiErrorResource::create(
                'Ошибка проверки статуса: ' . $e->getMessage(),
                500
            )->response();
        }
    }

}
