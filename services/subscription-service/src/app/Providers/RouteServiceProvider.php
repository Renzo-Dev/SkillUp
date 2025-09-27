<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Routing\Router;

class RouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        parent::boot();
    }

    public function map(): void
    {
        $this->mapApiRoutes($this->app['router']);
        $this->mapWebRoutes($this->app['router']);
    }

    protected function mapApiRoutes(Router $router): void
    {
        $router->prefix('api')
            ->middleware('api')
            ->group(base_path('routes/api.php'));
    }

    protected function mapWebRoutes(Router $router): void
    {
        $router->middleware('web')
            ->group(base_path('routes/web.php'));
    }
}

