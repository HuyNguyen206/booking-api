<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyResource extends JsonResource
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
            'address' => $this->getFullAddress(),
            'lat' => $this->lat,
            'long' => $this->long,
            'apartments' => $this->whenLoaded('apartments', ApartmentResource::collection($this->apartments ?? []))
        ];
    }
}
