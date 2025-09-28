<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\PlanFeature;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'code' => 'free',
                'name' => 'Free',
                'description' => 'Базовый доступ без оплаты',
                'price_cents' => 0,
                'currency' => 'USD',
                'billing_cycle' => 'monthly',
                'trial_period_days' => null,
                'is_active' => true,
                'features' => [
                    ['feature_key' => 'ai_requests_per_month', 'limit_value' => 20],
                    ['feature_key' => 'storage_gb', 'limit_value' => 1],
                ],
            ],
            [
                'code' => 'pro',
                'name' => 'Pro',
                'description' => 'Расширенные лимиты и priority поддержка',
                'price_cents' => 1999,
                'currency' => 'USD',
                'billing_cycle' => 'monthly',
                'trial_period_days' => 14,
                'is_active' => true,
                'features' => [
                    ['feature_key' => 'ai_requests_per_month', 'limit_value' => 500],
                    ['feature_key' => 'storage_gb', 'limit_value' => 100],
                    ['feature_key' => 'priority_support', 'limit_value' => null],
                ],
            ],
            [
                'code' => 'premium',
                'name' => 'Premium',
                'description' => 'Максимальные лимиты и доступ к бетам',
                'price_cents' => 4999,
                'currency' => 'USD',
                'billing_cycle' => 'monthly',
                'trial_period_days' => 30,
                'is_active' => true,
                'features' => [
                    ['feature_key' => 'ai_requests_per_month', 'limit_value' => null],
                    ['feature_key' => 'storage_gb', 'limit_value' => 1024],
                    ['feature_key' => 'beta_access', 'limit_value' => null],
                ],
            ],
        ];

        foreach ($plans as $planData) {
            $features = $planData['features'];
            unset($planData['features']);

            $plan = Plan::updateOrCreate(
                ['code' => $planData['code']],
                array_merge($planData, ['id' => Str::uuid()->toString()]),
            );

            foreach ($features as $feature) {
                PlanFeature::updateOrCreate(
                    ['plan_id' => $plan->id, 'feature_key' => $feature['feature_key']],
                    array_merge($feature, ['id' => Str::uuid()->toString()]),
                );
            }
        }
    }
}
