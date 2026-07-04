<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'service_number' => $this->service_number,
            'status' => $this->status,
            'name' => $this->name,
            'billing_type' => $this->billing_type,
            'billing_period' => $this->billing_period,
            'billing_interval' => $this->billing_interval,
            'price' => $this->price,
            'setup_fee' => $this->setup_fee,
            'currency' => $this->currency,
            'cycle_label' => $this->cycle_label,
            'activated_at' => $this->activated_at,
            'next_due_date' => $this->next_due_date,
            'suspended_at' => $this->suspended_at,
            'terminated_at' => $this->terminated_at,
            'cancelled_at' => $this->cancelled_at,
            'plugin_id' => $this->plugin_id,
            'variant_selections' => $this->variant_selections,
            'configuration' => $this->configuration,
            'user' => new UserResource($this->whenLoaded('user')),
            'package' => new PackageResource($this->whenLoaded('package')),
            'scalings' => ServiceScalingResource::collection($this->whenLoaded('scalings')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
