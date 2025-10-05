<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Регистрируем кастомный JWT middleware
        $middleware->alias([
            'guard.jwt' => \App\Http\Middleware\JwtAuthMiddleware::class,
        ]);
    })
    ->withSchedule(function (\Illuminate\Console\Scheduling\Schedule $schedule): void {
        // Очистка истекших refresh токенов каждые 6 часов в 0 минут
        $schedule->command('tokens:cleanup-refresh')
            ->cron('0 */6 * * *')
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/cleanup-refresh.log'));

        // Очистка истекших email токенов каждые 6 часов в 30 минут (смещение)
        $schedule->command('tokens:cleanup-email')
            ->cron('30 */6 * * *')
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/cleanup-email.log'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
