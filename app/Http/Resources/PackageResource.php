<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PackageResource extends JsonResource
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
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'icon' => $this->icon,
            'status' => $this->status,
            'stock' => $this->stock,
            'per_user_limit' => $this->per_user_limit,
            'allow_cancellation' => $this->allow_cancellation,
            'allow_quantity' => $this->allow_quantity,
            'prorata_day' => $this->prorata_day,
            'prorata_next_month_day' => $this->prorata_next_month_day,
            'auto_provision' => $this->auto_provision,
            'sort_order' => $this->sort_order,
            'plugin_id' => $this->plugin_id,
            'provisioning_config' => $this->provisioning_config,
            'catalog' => new CatalogResource($this->whenLoaded('catalog')),
            'prices' => PackagePriceResource::collection($this->whenLoaded('prices')),
            'variants' => VariantResource::collection($this->whenLoaded('variants')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
