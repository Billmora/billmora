<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VariantOptionResource extends JsonResource
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
            'prices' => VariantPriceResource::collection($this->whenLoaded('prices')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
