<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\JwtService;
use App\Services\UserService;
use App\Services\AuthService;
use App\Services\RefreshTokenService;
use App\Services\BlacklistService;
use App\Repositories\UserRepository;
use App\Repositories\RefreshTokenRepository;
use App\Contracts\JwtServiceInterface;
use App\Contracts\UserServiceInterface;
use App\Contracts\AuthServiceInterface;
use App\Contracts\RefreshTokenServiceInterface;
use App\Contracts\BlacklistServiceInterface;
use App\Contracts\UserRepositoryInterface;
use App\Contracts\RefreshTokenRepositoryInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Регистрация сервисов приложения
     */
    public function register(): void
    {
        // Привязываем интерфейсы к конкретным реализациям (DI)
        $this->app->bind(JwtServiceInterface::class, JwtService::class); // JWT сервис
        $this->app->bind(UserServiceInterface::class, UserService::class); // Работа с пользователями
        $this->app->bind(AuthServiceInterface::class, AuthService::class); // Аутентификация
        $this->app->bind(RefreshTokenServiceInterface::class, RefreshTokenService::class); // Refresh токены
        $this->app->bind(BlacklistServiceInterface::class, BlacklistService::class); // Блэклист токенов
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class); // Репозиторий пользователей
        $this->app->bind(RefreshTokenRepositoryInterface::class, RefreshTokenRepository::class); // Репозиторий refresh токенов
    }

    /**
     * Загрузка сервисов приложения
     */
    public function boot(): void
    {
        // Здесь можно добавить инициализацию событий, кастомных валидаторов и т.д.
        // Пока ничего не требуется
    }
}
