<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TldResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                     => $this->id,
            'tld'                    => $this->tld,
            'status'                 => $this->status,
            'plugin_id'              => $this->plugin_id,
            'min_years'              => $this->min_years,
            'max_years'              => $this->max_years,
            'grace_period_days'      => $this->grace_period_days,
            'redemption_period_days' => $this->redemption_period_days,
            'sort_order'             => $this->sort_order,
            'auto_renew'             => $this->auto_renew,
            'prices'                 => TldPriceResource::collection($this->whenLoaded('prices')),
            'created_at'             => $this->created_at,
            'updated_at'             => $this->updated_at,
        ];
    }
}
