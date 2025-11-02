<?php

namespace App\Contracts\Services;

interface BlackListServiceInterface
{
    public function addTokenToBlackList(string $token): bool; // Добавление токена в черный список
    public function checkTokenInBlackList(string $token): bool; // Проверка токена в черном списке
}