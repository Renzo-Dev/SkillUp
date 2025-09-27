<?php

namespace App\Http\Controllers;

use App\Http\Resources\SubscriptionResource;
use App\Services\Subscriptions\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionStatusController
{
    public function __construct(private SubscriptionService $subscriptionService)
    {
    }

    public function show(Request $request): JsonResponse
    {
        $claims = $request->attributes->get('jwt', []);
        $userId = $claims['sub'] ?? null;

        if (!$userId) {
            return response()->json(['error' => 'user_not_found'], 404);
        }

        $subscription = $this->subscriptionService->getSubscriptionForUser($userId);

        if (!$subscription) {
            return response()->json(['status' => 'free'], 200);
        }

        return (new SubscriptionResource($subscription->loadMissing(['plan.features', 'usageCounters'])))->response();
    }
}

