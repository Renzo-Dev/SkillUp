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
use App\Contracts\BlacklistServiceInterface;
use App\Services\BlacklistService;

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
        $this->app->bind(BlacklistServiceInterface::class, BlacklistService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Пока ничего не требуется
    }
}
