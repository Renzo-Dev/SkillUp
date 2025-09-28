<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Services\JwtService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Автоматическая очистка истекших refresh токенов каждый час
Schedule::call(function () {
    $jwtService = app(JwtService::class);
    $jwtService->cleanupExpiredTokens();
})->hourly()->name('cleanup-expired-tokens');
