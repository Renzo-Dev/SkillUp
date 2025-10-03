<?php

namespace App\Console\Commands;

use App\Contracts\RefreshTokenServiceInterface;
use Illuminate\Console\Command;

class CleanupExpiredRefreshTokens extends Command
{
    protected $signature = 'refresh:cleanup-expired';
    protected $description = 'Удалить истекшие refresh токены';

    public function __construct(private RefreshTokenServiceInterface $service)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $deleted = $this->service->cleanExpiredTokens();
        $this->info("Удалено истекших refresh токенов: {$deleted}");
        return self::SUCCESS;
    }
}


