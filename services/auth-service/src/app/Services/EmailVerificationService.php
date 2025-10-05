<?php

namespace App\Services;

use App\Contracts\Services\EmailVerificationServiceInterface;
use App\Contracts\Services\CustomLoggerInterface;
use App\Contracts\Services\UserServiceInterface;
use App\Events\EmailEventPublisher;
use App\Models\User;
use App\Models\EmailVerification;
use Illuminate\Support\Str;
use Carbon\Carbon;

class EmailVerificationService implements EmailVerificationServiceInterface
{
    public function __construct(
        private CustomLoggerInterface $logger,
        private EmailEventPublisher $emailEventPublisher,
        private UserServiceInterface $userService
    ) {}

    /**
     * Создание токена верификации для пользователя
     */
    public function createVerificationToken(User $user, string $email): ?EmailVerification
    {
        try {
            // Удаляем старые токены для этого email
            $this->revokeAllTokens($user);

            // Создаем новый токен
            $token = EmailVerification::create([
                'user_id' => $user->id,
                'email' => $email,
                'token' => Str::random(64),
                'expires_at' => Carbon::now()->addHours(24), // Токен действует 24 часа
            ]);

            $this->logger->serviceInfo("Создан токен верификации для пользователя {$user->id}, email: {$email}");
            
            // Отправляем событие о создании токена верификации
            $this->emailEventPublisher->publishEmailVerificationRequested($user, $token);
            
            return $token;

        } catch (\Exception $e) {
            $this->logger->serviceError("Ошибка создания токена верификации: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Проверка токена верификации
     */
    public function verifyToken(string $token): ?EmailVerification
    {
        try {
            $verification = EmailVerification::where('token', $token)
                ->where('expires_at', '>', Carbon::now())
                ->first();

            if ($verification) {
                $this->logger->serviceInfo("Токен верификации найден: {$token}");
            } else {
                $this->logger->serviceWarning("Токен верификации не найден или истек: {$token}");
            }

            return $verification;

        } catch (\Exception $e) {
            $this->logger->serviceError("Ошибка проверки токена: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Подтверждение email пользователя
     */
    public function confirmEmail(string $token): bool
    {
        try {
            $verification = $this->verifyToken($token);
            
            if (!$verification) {
                return false;
            }

            // Отмечаем токен как использованный
            $verification->update([
                'verified_at' => Carbon::now()
            ]);

            // Обновляем поле email_verified_at через UserService
            $user = User::find($verification->user_id);
            if ($user) {
                $updatedUser = $this->userService->verifyEmail($user);
                
                if ($updatedUser) {
                    $this->logger->serviceInfo("Email подтвержден для пользователя {$verification->user_id} через UserService");
                    
                    // Отправляем событие о подтверждении email
                    $this->emailEventPublisher->publishEmailVerificationCompleted($updatedUser, $verification);
                } else {
                    $this->logger->serviceError("Ошибка обновления email_verified_at через UserService для пользователя {$verification->user_id}");
                }
            } else {
                $this->logger->serviceError("Пользователь не найден для обновления email_verified_at: {$verification->user_id}");
            }
            
            return true;

        } catch (\Exception $e) {
            $this->logger->serviceError("Ошибка подтверждения email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Проверка, верифицирован ли email пользователя
     */
    public function isEmailVerified(User $user, string $email): bool
    {
        try {
            // Проверяем поле email_verified_at в таблице пользователей
            if ($user->email_verified_at) {
                return true;
            }

            // Дополнительная проверка через таблицу верификации (для совместимости)
            $verified = EmailVerification::where('user_id', $user->id)
                ->where('email', $email)
                ->whereNotNull('verified_at')
                ->exists();

            return $verified;

        } catch (\Exception $e) {
            $this->logger->serviceError("Ошибка проверки верификации email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Получение активного токена верификации для пользователя
     */
    public function getActiveToken(User $user, string $email): ?EmailVerification
    {
        try {
            return EmailVerification::where('user_id', $user->id)
                ->where('email', $email)
                ->where('expires_at', '>', Carbon::now())
                ->whereNull('verified_at')
                ->first();

        } catch (\Exception $e) {
            $this->logger->serviceError("Ошибка получения активного токена: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Удаление истекших токенов для пользователя
     */
    public function cleanupExpiredTokens(User $user): int
    {
        try {
            $deletedCount = EmailVerification::where('user_id', $user->id)
                ->where('expires_at', '<', Carbon::now())
                ->delete();

            if ($deletedCount > 0) {
                $this->logger->serviceInfo("Удалено истекших токенов для пользователя {$user->id}: {$deletedCount}");
            }

            return $deletedCount;

        } catch (\Exception $e) {
            $this->logger->serviceError("Ошибка очистки токенов: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Удаление всех токенов для пользователя
     */
    public function revokeAllTokens(User $user): bool
    {
        try {
            $deletedCount = EmailVerification::where('user_id', $user->id)->delete();
            
            $this->logger->serviceInfo("Удалено токенов для пользователя {$user->id}: {$deletedCount}");
            return true;

        } catch (\Exception $e) {
            $this->logger->serviceError("Ошибка удаления токенов: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Проверка валидности токена (не истек)
     */
    public function isTokenValid(EmailVerification $token): bool
    {
        return $token->expires_at && $token->expires_at->isFuture();
    }

    /**
     * Получение токена по ID
     */
    public function findTokenById(int $id): ?EmailVerification
    {
        try {
            return EmailVerification::find($id);
        } catch (\Exception $e) {
            $this->logger->serviceError("Ошибка поиска токена по ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Обновление токена
     */
    public function updateToken(EmailVerification $token, array $data): ?EmailVerification
    {
        try {
            $token->update($data);
            $this->logger->serviceInfo("Токен обновлен: {$token->id}");
            return $token;
        } catch (\Exception $e) {
            $this->logger->serviceError("Ошибка обновления токена: " . $e->getMessage());
            return null;
        }
    }
}
