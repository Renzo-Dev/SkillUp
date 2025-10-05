<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EmailVerification;
use Illuminate\Support\Facades\Log;

/**
 * Команда для очистки истекших email токенов верификации
 */
class CleanupExpiredEmailTokens extends Command
{
    protected $signature = 'tokens:cleanup-email';
    protected $description = 'Очистка истекших email токенов верификации';

    public function handle(): int
    {
        $this->info('Начинаем очистку истекших email токенов...');
        
        try {
            // Удаляем токены, которые истекли
            $deletedCount = EmailVerification::where('expires_at', '<', now())->delete();
            
            $this->info("Удалено истекших email токенов: {$deletedCount}");
            Log::info("Очистка email токенов завершена. Удалено: {$deletedCount}");
            
            return 0;
        } catch (\Exception $e) {
            $this->error("Ошибка при очистке email токенов: " . $e->getMessage());
            Log::error("Ошибка очистки email токенов: " . $e->getMessage());
            return 1;
        }
    }
}
