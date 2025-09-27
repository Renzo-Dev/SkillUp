<?php

namespace App\Services\Plans;

use App\Models\Plan;
use App\Models\PlanFeature;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class PlanService
{
    public function __construct(private DatabaseManager $db)
    {
    }

    public function getActivePlanByCode(string $code): ?Plan
    {
        return Plan::query()
            ->with('features')
            ->where('code', $code)
            ->where('is_active', true)
            ->first();
    }

    public function listActivePlans(): array
    {
        return Plan::query()
            ->where('is_active', true)
            ->orderBy('price_cents')
            ->get()
            ->all();
    }

    public function create(array $data): Plan
    {
        return $this->db->transaction(function () use ($data) {
            $features = Arr::pull($data, 'features', []);

            $plan = Plan::create(array_merge($data, ['id' => Str::uuid()->toString()]));
            $this->syncFeatures($plan, $features);

            return $plan->load('features');
        });
    }

    public function update(Plan $plan, array $data): Plan
    {
        return $this->db->transaction(function () use ($plan, $data) {
            $features = Arr::pull($data, 'features', null);
            $plan->update($data);

            if ($features !== null) {
                $this->syncFeatures($plan, $features);
            }

            return $plan->fresh('features');
        });
    }

    private function syncFeatures(Plan $plan, array $features): void
    {
        $plan->features()->delete();

        foreach ($features as $feature) {
            $plan->features()->create(array_merge($feature, ['id' => Str::uuid()->toString()]));
        }
    }
}

