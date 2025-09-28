<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(\App\Services\Jwt\JwtVerifier::class, function ($app) {
            return new \App\Services\Jwt\JwtVerifier(
                cache: $app->make(\Illuminate\Contracts\Cache\Repository::class),
                httpClient: $app->make(\GuzzleHttp\Client::class),
                config: $app->make('config')->get('jwt')
            );
        });

        $this->app->singleton(\App\Services\RabbitMQ\EventPublisher::class, function ($app) {
            return new \App\Services\RabbitMQ\EventPublisher(
                config('rabbitmq.outgoing'),
                $app->make(\Illuminate\Contracts\Logging\Log::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\URL::forceRootUrl(config('app.url'));
    }
}
