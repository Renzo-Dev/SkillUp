<?php

namespace App\Services\Subscriptions;

use App\Models\Subscription;
use App\Models\UsageCounter;
use App\Services\RabbitMQ\EventPublisher;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SubscriptionService
{
    public function __construct(
        private CacheRepository $cache,
        private DatabaseManager $db,
        private EventPublisher $eventPublisher,
    ) {
    }

    public function getSubscriptionForUser(string $userId): ?Subscription
  {
        $cacheKey = sprintf('subscription:%s:status', $userId);

        return $this->cache->remember($cacheKey, now()->addMinutes(5), function () use ($userId) {
            return Subscription::query()
                ->with(['plan.features', 'usageCounters'])
                ->where('user_id', $userId)
                ->latest('created_at')
                ->first();
        });
    }

    public function adjustUsage(string $subscriptionId, string $featureKey, int $amount): UsageCounter
    {
        return $this->db->transaction(function () use ($subscriptionId, $featureKey, $amount) {
            $counter = UsageCounter::query()
                ->lockForUpdate()
                ->where('subscription_id', $subscriptionId)
                ->where('feature_key', $featureKey)
                ->first();

            if (!$counter) {
                throw new \RuntimeException('usage_counter_not_found');
            }

            if ($counter->limit_value !== null && $counter->used_amount + $amount > $counter->limit_value) {
                throw new \RuntimeException('limit_exceeded');
            }

            $counter->used_amount += $amount;
            $counter->save();

            return $counter;
        });
    }

    public function invalidateCache(string $userId): void
    {
        $this->cache->forget(sprintf('subscription:%s:status', $userId));
  }

    public function processPaymentSuccess(array $payload): void
    {
        $this->db->transaction(function () use ($payload) {
            $subscription = $this->findOrCreateSubscription($payload);
            $subscription->status = 'active';
            $subscription->last_payment_id = $payload['payment_id'] ?? null;
            $subscription->started_at = now();
            $subscription->expires_at = now()->addMonth();
            $subscription->save();

            $this->recordEvent($subscription, 'payment_success', $payload);
            $this->eventPublisher->publish('subscription.activated', $this->buildEventPayload($subscription, $payload));

            $this->invalidateCache($subscription->user_id);
        });
    }

    public function processPaymentFailed(array $payload): void
    {
        $this->db->transaction(function () use ($payload) {
            $subscription = $this->findOrCreateSubscription($payload);
            $subscription->status = 'grace';
            $subscription->save();

            $this->recordEvent($subscription, 'payment_failed', $payload);
            $this->eventPublisher->publish('subscription.payment_failed', $this->buildEventPayload($subscription, $payload));

            $this->invalidateCache($subscription->user_id);
        });
    }

    public function processSubscriptionRenewed(array $payload): void
    {
        $this->db->transaction(function () use ($payload) {
            $subscription = $this->findOrCreateSubscription($payload);
            $subscription->status = 'active';
            $subscription->expires_at = now()->addMonth();
            $subscription->save();

            $this->recordEvent($subscription, 'subscription_renewed', $payload);
            $this->eventPublisher->publish('subscription.renewed', $this->buildEventPayload($subscription, $payload));

            $this->invalidateCache($subscription->user_id);
        });
    }

    public function processSubscriptionCancelled(array $payload): void
    {
        $this->db->transaction(function () use ($payload) {
            $subscription = $this->findOrCreateSubscription($payload);
            $subscription->status = 'cancelled';
            $subscription->cancelled_at = now();
            $subscription->save();

            $this->recordEvent($subscription, 'subscription_cancelled', $payload);
            $this->eventPublisher->publish('subscription.cancelled', $this->buildEventPayload($subscription, $payload));

            $this->invalidateCache($subscription->user_id);
        });
    }

    private function findOrCreateSubscription(array $payload): Subscription
    {
        return Subscription::query()->firstOrCreate(
            ['user_id' => $payload['user_id'] ?? Str::uuid()->toString()],
            [
                'id' => Str::uuid()->toString(),
                'plan_id' => $payload['plan_id'] ?? null,
                'status' => 'trial',
                'source' => 'payment_service',
            ]
        );
    }

    private function recordEvent(Subscription $subscription, string $type, array $payload): void
    {
        $subscription->events()->create([
            'id' => Str::uuid()->toString(),
            'event_type' => $type,
            'payload' => $payload,
            'processed_at' => now(),
            'correlation_id' => $payload['correlation_id'] ?? Str::uuid()->toString(),
            'event_id' => $payload['event_id'] ?? Str::uuid()->toString(),
        ]);
    }

    private function buildEventPayload(Subscription $subscription, array $payload): array
    {
        return [
            'event_id' => $payload['event_id'] ?? Str::uuid()->toString(),
            'correlation_id' => $payload['correlation_id'] ?? Str::uuid()->toString(),
            'occurred_at' => now()->toIso8601String(),
            'subscription' => [
                'id' => $subscription->id,
                'user_id' => $subscription->user_id,
                'plan_id' => $subscription->plan_id,
                'status' => $subscription->status,
                'expires_at' => $subscription->expires_at,
            ],
            'raw' => $payload,
        ];
    }
}

