<?php

namespace App\Console\Commands;

use App\Contracts\EmailVerificationServiceInterface;
use Illuminate\Console\Command;

class CleanupExpiredEmailTokens extends Command
{
    protected $signature = 'email:cleanup-expired';
    protected $description = 'Удалить истекшие токены подтверждения email';

    public function __construct(private EmailVerificationServiceInterface $service)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $deleted = $this->service->cleanupExpiredTokens();
        $this->info("Удалено записей: {$deleted}");
        return self::SUCCESS;
    }
}


