<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RefreshToken;
use Illuminate\Support\Facades\Log;

/**
 * Команда для очистки истекших refresh токенов
 */
class CleanupExpiredRefreshTokens extends Command
{
    protected $signature = 'tokens:cleanup-refresh';
    protected $description = 'Очистка истекших refresh токенов';

    public function handle(): int
    {
        $this->info('Начинаем очистку истекших refresh токенов...');
        
        try {
            // Удаляем токены, которые истекли
            $deletedCount = RefreshToken::where('expires_at', '<', now())->delete();
            
            $this->info("Удалено истекших refresh токенов: {$deletedCount}");
            Log::info("Очистка refresh токенов завершена. Удалено: {$deletedCount}");
            
            return 0;
        } catch (\Exception $e) {
            $this->error("Ошибка при очистке refresh токенов: " . $e->getMessage());
            Log::error("Ошибка очистки refresh токенов: " . $e->getMessage());
            return 1;
        }
    }
}
