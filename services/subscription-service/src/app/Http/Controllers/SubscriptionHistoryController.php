<?php

namespace App\Http\Controllers;

use App\Http\Resources\SubscriptionEventResource;
use App\Services\Subscriptions\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionHistoryController extends Controller
{
    public function __construct(private SubscriptionService $subscriptionService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $claims = $request->attributes->get('jwt', []);
        $userId = $claims['sub'] ?? null;

        $subscription = $userId ? $this->subscriptionService->getSubscriptionForUser($userId) : null;

        if (!$subscription) {
            return response()->json(['data' => []]);
        }

        $events = $subscription->events()->orderByDesc('created_at')->paginate(25);

        return SubscriptionEventResource::collection($events)->response();
    }
}
