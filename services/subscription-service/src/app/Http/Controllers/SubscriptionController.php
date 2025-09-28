<?php

namespace App\Http\Controllers;

use App\Http\Requests\CancelSubscriptionRequest;
use App\Http\Requests\ChangePlanRequest;
use App\Http\Requests\UsageLimitsRequest;
use App\Http\Requests\UsageRequest;
use App\Http\Resources\SubscriptionResource;
use App\Http\Resources\UsageCounterResource;
use App\Services\Plans\PlanService;
use App\Services\Subscriptions\SubscriptionService;
use App\Services\Usage\UsageService;
use App\Services\Payment\PaymentService;
use App\Support\AuthContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function __construct(
        private SubscriptionService $subscriptionService,
        private PlanService $planService,
        private UsageService $usageService,
        private PaymentService $paymentService,
    ) {
    }

    public function change(ChangePlanRequest $request): JsonResponse
    {
        $userId = AuthContext::userId($request);
        $plan = $this->planService->getActivePlanByCode($request->validated()['plan_code']);

        if (!$plan) {
            return response()->json(['error' => 'plan_not_found'], 404);
        }

        $response = $this->paymentService->initiateChange([
            'user_id' => $userId,
            'plan_code' => $plan->code,
            'payment_token' => $request->validated()['payment_token'] ?? null,
        ]);

        return response()->json(['status' => $response['status'] ?? 'pending']);
    }

    public function cancel(CancelSubscriptionRequest $request): JsonResponse
    {
        $userId = AuthContext::userId($request);
        $subscription = $this->subscriptionService->getSubscriptionForUser($userId);

        if (!$subscription) {
            return response()->json(['error' => 'subscription_not_found'], 404);
        }

        $subscription->status = 'cancelled';
        $subscription->cancelled_at = now();
        $subscription->cancellation_reason = $request->validated()['reason'] ?? null;
        $subscription->auto_renew = false;
        $subscription->save();

        $this->subscriptionService->invalidateCache($subscription->user_id);

        return (new SubscriptionResource($subscription))->response();
    }

    public function limits(UsageLimitsRequest $request): JsonResponse
    {
        $subscription = $this->subscriptionService->getSubscriptionForUser(AuthContext::userId($request));

        if (!$subscription) {
            return response()->json(['limits' => []]);
        }

        return UsageCounterResource::collection($subscription->usageCounters)->response();
    }

    public function usage(UsageRequest $request): JsonResponse
    {
        $subscription = $this->subscriptionService->getSubscriptionForUser(AuthContext::userId($request));

        if (!$subscription) {
            return response()->json(['error' => 'subscription_not_found'], 404);
        }

        $counter = $this->usageService->consume($subscription, $request->validated()['feature_key'], $request->validated()['amount']);

        return (new UsageCounterResource($counter))->response();
    }
}
