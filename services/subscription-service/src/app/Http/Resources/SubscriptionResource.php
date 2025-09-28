<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'plan' => new PlanResource($this->whenLoaded('plan')),
            'expires_at' => $this->expires_at,
            'trial_ends_at' => $this->trial_ends_at,
            'cancelled_at' => $this->cancelled_at,
        ];
    }
}

