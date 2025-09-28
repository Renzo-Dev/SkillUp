<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UsageCounterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'feature_key' => $this->feature_key,
            'used_amount' => $this->used_amount,
            'limit_value' => $this->limit_value,
            'period_start' => $this->period_start,
            'period_end' => $this->period_end,
        ];
    }
}
