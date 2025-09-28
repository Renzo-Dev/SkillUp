<?php

namespace App\Services\Usage;

use App\Models\Subscription;
use App\Models\UsageCounter;
use Illuminate\Database\DatabaseManager;

class UsageService
{
    public function __construct(private DatabaseManager $db)
    {
    }

    public function consume(Subscription $subscription, string $featureKey, int $amount): UsageCounter
    {
        return $this->db->transaction(function () use ($subscription, $featureKey, $amount) {
            $counter = UsageCounter::query()
                ->lockForUpdate()
                ->where('subscription_id', $subscription->id)
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
}

