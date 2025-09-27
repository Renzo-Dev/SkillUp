<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;

class PaymentService
{
    public function initiateChange(array $payload): array
    {
        return ['status' => 'pending'];
    }

    public function syncStatus(string $paymentId): array
    {
        return ['status' => 'synced'];
    }
}

