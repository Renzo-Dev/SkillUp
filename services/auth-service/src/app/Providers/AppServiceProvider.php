<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\JwtService;
use App\Services\UserService;
use App\Services\AuthService;
use App\Services\RefreshTokenService;
use App\Models\UserRefreshToken;
use App\Contracts\JwtServiceInterface;
use App\Contracts\UserServiceInterface;
use App\Contracts\AuthServiceInterface;
use App\Contracts\RefreshTokenServiceInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Привязка интерфейсов к реализациям
        $this->app->bind(JwtServiceInterface::class, JwtService::class);
        $this->app->bind(UserServiceInterface::class, UserService::class);
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->bind(RefreshTokenServiceInterface::class, RefreshTokenService::class);

        // Конкретная реализация JwtService с параметрами из конфига
        $this->app->bind(JwtService::class, function($app){
            return new JwtService(
                $app->make('config')->get('jwt.secret'),
                $app->make('config')->get('jwt.ttl'),
                $app->make('config')->get('jwt.refresh_ttl')
            );
        });

        // Для остальных сервисов достаточно указать класс
        $this->app->bind(UserService::class);
        $this->app->bind(AuthService::class);
        $this->app->bind(UserRefreshToken::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Пока ничего не требуется
    }
}
