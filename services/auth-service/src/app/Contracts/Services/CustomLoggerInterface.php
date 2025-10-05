<?php

namespace App\Contracts\Services;

interface CustomLoggerInterface
{
    /**
     * Логирование ошибки с контекстом
     */
    public function error(string $message, array $context = []): void;

    /**
     * Логирование ошибки контроллера (автоматически добавляет класс и метод)
     */
    public function controllerError(string $message, array $context = []): void;

    /**
     * Логирование ошибки сервиса
     */
    public function serviceError(string $message, array $context = []): void;

    /**
     * Логирование ошибки репозитория
     */
    public function repositoryError(string $message, array $context = []): void;

    /**
     * Логирование с уровнем
     */
    public function log(string $level, string $message, array $context = []): void;
}
