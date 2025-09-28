<?php

namespace App\Jobs;

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Services\Subscriptions\SubscriptionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PaymentEventJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private array $payload)
    {
    }

    public function handle(SubscriptionService $subscriptionService): void
    {
        $type = $this->payload['event_type'] ?? null;

        if (!$type) {
            Log::warning('payment_event_missing_type', $this->payload);
            return;
        }

        match ($type) {
            'payment_success' => $subscriptionService->processPaymentSuccess($this->payload),
            'payment_failed' => $subscriptionService->processPaymentFailed($this->payload),
            'subscription_renewed' => $subscriptionService->processSubscriptionRenewed($this->payload),
            'subscription_cancelled' => $subscriptionService->processSubscriptionCancelled($this->payload),
            default => Log::info('payment_event_ignored', ['type' => $type]),
        };
    }
}

