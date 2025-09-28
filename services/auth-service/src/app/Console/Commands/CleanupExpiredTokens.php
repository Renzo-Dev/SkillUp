<?php

namespace App\Console\Commands;

use App\Services\JwtService;
use Illuminate\Console\Command;

class CleanupExpiredTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tokens:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Очистка истекших refresh токенов';

    /**
     * Execute the console command.
     */
    public function handle(JwtService $jwtService)
    {
        $this->info('Начинаем очистку истекших токенов...');
        
        // Получаем количество токенов до очистки
        $beforeCount = \App\Models\RefreshToken::count();
        $expiredCount = \App\Models\RefreshToken::where('expires_at', '<', now())->count();
        
        $this->info("Найдено истекших токенов: {$expiredCount}");
        
        if ($expiredCount > 0) {
            // Выполняем очистку
            $jwtService->cleanupExpiredTokens();
            
            // Получаем количество после очистки
            $afterCount = \App\Models\RefreshToken::count();
            $cleanedCount = $beforeCount - $afterCount;
            
            $this->info("Очищено токенов: {$cleanedCount}");
            $this->info("Осталось токенов: {$afterCount}");
        } else {
            $this->info('Истекших токенов не найдено');
        }
        
        $this->info('Очистка завершена!');
    }
}
