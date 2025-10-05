<?php

namespace App\Services;

use App\Contracts\Services\CustomLoggerInterface;
use Illuminate\Support\Facades\Log;

/**
 * Кастомный логгер для структурированного логирования
 */
class CustomLoggerService implements CustomLoggerInterface
{
    /**
     * Логирование ошибки с контекстом
     */
    public function error(string $message, array $context = []): void
    {
        Log::error($message, $context);
    }

    /**
     * Логирование ошибки контроллера (автоматически добавляет класс и метод)
     */
    public function controllerError(string $message, array $context = []): void
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $caller = $backtrace[1] ?? null;
        
        $class = $caller['class'] ?? 'Unknown';
        $method = $caller['function'] ?? 'Unknown';
        
        $logMessage = sprintf('%s@%s: %s', $class, $method, $message);
        Log::error($logMessage);
    }

    /**
     * Логирование ошибки сервиса
     */
    public function serviceError(string $message, array $context = []): void
    {
        Log::error($message);
    }

    /**
     * Логирование ошибки репозитория
     */
    public function repositoryError(string $message, array $context = []): void
    {
        Log::error($message);
    }

    /**
     * Логирование с уровнем
     */
    public function log(string $level, string $message, array $context = []): void
    {
        Log::log($level, $message, $context);
    }
}
