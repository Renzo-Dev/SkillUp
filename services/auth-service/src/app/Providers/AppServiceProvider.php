<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\Services\AuthServiceInterface;
use App\Services\AuthService;
use App\Contracts\Services\UserServiceInterface;
use App\Services\UserService;
use App\Contracts\Services\BlackListServiceInterface;
use App\Services\BlackListService;
use App\Contracts\Services\JwtServiceInterface;
use App\Services\JwtService;
use App\Contracts\Services\JwtMetadataCacheServiceInterface;
use App\Services\JwtMetadataCacheService;
use App\Contracts\Services\CustomLoggerInterface;
use App\Services\CustomLoggerService;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Repositories\UserRepository;
use App\Contracts\Repositories\RefreshTokenRepositoryInterface;
use App\Repositories\RefreshTokenRepository;
use App\Contracts\Services\TokenServiceInterface;
use App\Services\TokenService;
use App\Contracts\Services\RabbitMQServiceInterface;
use App\Services\RabbitMQService;
use App\Contracts\Services\EmailVerificationServiceInterface;
use App\Services\EmailVerificationService;
use App\Support\JWT\JwtManager;
use App\Support\JWT\JwtPayloadFactory;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // JWT Infrastructure (singleton для переиспользования ключей)
        $this->app->singleton(JwtManager::class, function ($app) {
            return new JwtManager(
                privateKeyPath: config('jwt.keys.private'),
                publicKeyPath: config('jwt.keys.public'),
                algorithm: config('jwt.algo', 'RS256')
            );
        });
        
        $this->app->bind(JwtPayloadFactory::class);
        
        // Services
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->bind(UserServiceInterface::class, UserService::class);
        $this->app->bind(JwtServiceInterface::class, JwtService::class);
        $this->app->bind(CustomLoggerInterface::class, CustomLoggerService::class);
        $this->app->bind(BlackListServiceInterface::class, BlackListService::class);
        $this->app->bind(TokenServiceInterface::class, TokenService::class);
        $this->app->bind(RabbitMQServiceInterface::class, RabbitMQService::class);
        $this->app->bind(EmailVerificationServiceInterface::class, EmailVerificationService::class);
        $this->app->bind(JwtMetadataCacheServiceInterface::class, JwtMetadataCacheService::class);
        
        // Repositories
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(RefreshTokenRepositoryInterface::class, RefreshTokenRepository::class);
    }

    public function boot(): void
    {
        // Пока ничего не требуется
    }
}
