<?php

namespace App\Services;

use App\Services\JwtService;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
// use App\Http\Requests\LogoutRequest; // не реализовано
use App\Services\UserService;
// use App\Services\EventService; // не реализовано
// use App\Services\EmailService; // не реализовано
use App\Http\Resources\LogoutResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthService{
  public function __construct(
    protected JwtService $jwtService,
    // protected EmailService $emailService, // не реализовано
    protected UserService $userService
    // protected EventService $eventService // не реализовано
  ){
  }

  // Регистрация нового пользователя
  public function register($data){
    try {
      $user = $this->userService->createUser($data);

      // Генерируем токены
      $token = $this->jwtService->generateTokenPair($user);

      // Возвращаем данные с токенами
      return [
        'user' => $user,
        'access_token' => $token->accessToken,
        'refresh_token' => $token->refreshToken,
      ];

    } catch (\Exception $e) {
      #TODO: Логирование ошибки
      return false;
    }
  }

  // Вход пользователя
  public function login(LoginRequest $request){}

  // Выход пользователя
  public function logout() {
    try {
      // Получаем JWT из заголовка Authorization
      $token = request()->bearerToken();

      // Инвалидируем токен
      $this->jwtService->revokeToken($token);

      return true;

    } catch (\Exception $e) {
      return false;
    }
  }
}

