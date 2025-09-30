<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Contracts\AuthServiceInterface;
use App\DTOs\LoginRequestDTO;
use App\DTOs\RegisterRequestDTO;
use App\Http\Resources\LogoutResource;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\RegisterResource;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\LoginResource;

class AuthController extends Controller
{
    public function __construct(
        protected AuthServiceInterface $authService
    ) {}

    public function register(RegisterRequest $request)
    {
        $dto = RegisterRequestDTO::fromRequest($request);
        $authResponse = $this->authService->register($dto);
        
        if ($authResponse) {
            return new RegisterResource($authResponse); // Передаем DTO напрямую
        } else {
            Log::error('Ошибка регистрации в контроллере', [
                'request_data' => $request->validated(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
            return new RegisterResource(null); // Передаем null для ошибки
        }
    }

    public function login(LoginRequest $request)
    {
        $dto = LoginRequestDTO::fromRequest($request);
        $authResponse = $this->authService->login($dto);
        
        if ($authResponse) {
            return new LoginResource($authResponse); // Передаем DTO напрямую
        } else {
            Log::error('Ошибка логина в контроллере', [
                'request_data' => $request->validated(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
            return new LoginResource(null); // Передаем null для ошибки
        }
    }

    public function logout(Request $request)
    {
        $token = $request->bearerToken();
        if ($this->authService->logout($token)) {
            return new LogoutResource(true);
        } else {
            return new LogoutResource(false);
        }
    }

}
