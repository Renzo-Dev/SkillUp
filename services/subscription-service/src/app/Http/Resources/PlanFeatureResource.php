<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanFeatureResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'feature_key' => $this->feature_key,
            'limit_value' => $this->limit_value,
            'metadata' => $this->metadata,
        ];
    }
}

