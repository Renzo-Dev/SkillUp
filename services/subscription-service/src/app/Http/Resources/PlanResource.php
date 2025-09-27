<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'price_cents' => $this->price_cents,
            'currency' => $this->currency,
            'billing_cycle' => $this->billing_cycle,
            'trial_period_days' => $this->trial_period_days,
            'features' => PlanFeatureResource::collection($this->whenLoaded('features')),
        ];
    }
}

