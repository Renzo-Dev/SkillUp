<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\CreatePlanRequest;
use App\Http\Requests\Admin\UpdatePlanRequest;
use App\Http\Requests\Admin\AdjustSubscriptionRequest;
use App\Http\Resources\PlanResource;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\Plans\PlanService;
use App\Services\Subscriptions\SubscriptionService;
use Illuminate\Http\JsonResponse;

class SubscriptionAdminController extends Controller
{
    public function __construct(private PlanService $planService, private SubscriptionService $subscriptionService)
    {
    }

    public function store(CreatePlanRequest $request): JsonResponse
    {
        $plan = $this->planService->create($request->validated());

        return (new PlanResource($plan))->response()->setStatusCode(201);
    }

    public function update(UpdatePlanRequest $request, Plan $plan): JsonResponse
    {
        $updated = $this->planService->update($plan, $request->validated());

        return (new PlanResource($updated))->response();
    }

    public function adjust(AdjustSubscriptionRequest $request, Subscription $subscription): JsonResponse
    {
        $this->subscriptionService->adjustSubscription($subscription, $request->validated());

        return response()->json(['status' => 'updated']);
    }
}
