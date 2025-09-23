<?php

namespace App\Services;

use App\Models\User;
use App\Models\RefreshToken;
use App\Models\PasswordReset;
use App\Services\RabbitMQService;
use App\Services\JwtService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AuthService
{
  public function __construct(
    private RabbitMQService $rabbitMQService,
    private JwtService $jwtService
  ) {
  }

  /**
   * Создание нового пользователя и отправка события регистрации
   */
  public function register(array $data): User
  {
    $verificationToken = Str::random(64);

    $user = User::create([
      'name' => $data['name'],
      'email' => $data['email'],
      'password' => $data['password'],
      'role' => 'user',
      'is_active' => true,
      'email_verification_token' => $verificationToken,
    ]);

    $this->rabbitMQService->publish('user.registered', [
      'user_id' => $user->id,
      'email' => $user->email,
      'role' => $user->role,
      'name' => $user->name,
      'verification_token' => $verificationToken,
    ]);

    return $user;
  }

  /**
   * Аутентификация пользователя с проверкой пароля и статуса
   */
  public function login(array $data): User
  {
    $user = User::where('email', $data['email'])->firstOrFail();

    if (!Hash::check($data['password'], $user->password)) {
      throw new \Exception('Invalid credentials');
    }

    if (!$user->is_active) {
      throw new \Exception('Account is deactivated');
    }

    $user->update(['last_login_at' => now()]);

    $this->rabbitMQService->publish('user.logged_in', [
      'user_id' => $user->id,
      'ip_address' => request()->ip(),
    ]);

    return $user;
  }

  /**
   * Обновление токена доступа через refresh токен
   */
  public function refreshToken(string $refreshToken): User
  {
    $user = $this->jwtService->validateRefreshToken($refreshToken);

    if (!$user) {
      throw new \Exception('Invalid or expired refresh token');
    }

    if (!$user->is_active) {
      throw new \Exception('Account is deactivated');
    }

    // Удаляем использованный refresh токен
    $this->jwtService->revokeRefreshToken($refreshToken);

    return $user;
  }

  /**
   * Выход пользователя - отзыв всех токенов
   */
  public function logout(User $user): void
  {
    $this->jwtService->revokeAllUserTokens($user);
  }

  /**
   * Создание токена для сброса пароля
   */
  public function forgotPassword(string $email): void
  {
    $user = User::where('email', $email)->first();

    if (!$user) {
      return; // Don't reveal if email exists
    }

    $token = Str::random(64);
    $expiresAt = now()->addHours(1);

    PasswordReset::updateOrCreate(
      ['email' => $email],
      [
        'token' => $token,
        'expires_at' => $expiresAt,
      ]
    );

    // TODO: Send email with reset link
  }

  /**
   * Сброс пароля по токену
   */
  public function resetPassword(array $data): void
  {
    $passwordReset = PasswordReset::where('token', $data['token'])
      ->where('expires_at', '>', now())
      ->firstOrFail();

    $user = User::where('email', $passwordReset->email)->firstOrFail();

    $user->update(['password' => $data['password']]);
    $passwordReset->delete();
  }

  /**
   * Подтверждение email адреса по токену
   */
  public function verifyEmail(string $token): void
  {
    $user = User::where('email_verification_token', $token)->firstOrFail();
    
    // Обновляем статус подтверждения и удаляем токен
    $user->update([
      'email_verified_at' => now(),
      'email_verification_token' => null,
      'is_active' => true
    ]);

    // Отправляем событие подтверждения email для сервиса подписки
    $this->rabbitMQService->publish('user.email_verified', [
      'user_id' => $user->id,
      'email' => $user->email,
      'name' => $user->name,
      'role' => $user->role,
      'verified_at' => $user->email_verified_at->toISOString(),
    ]);
  }

  /**
   * Обновление профиля пользователя
   */
  public function updateProfile(User $user, array $data): User
  {
    $user->update($data);
    return $user->fresh();
  }

  /**
   * Изменение пароля с проверкой текущего
   */
  public function updatePassword(User $user, array $data): void
  {
    if (!Hash::check($data['current_password'], $user->password)) {
      throw new \Exception('Current password is incorrect');
    }

    $user->update(['password' => $data['new_password']]);
  }
}
