<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApartmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'type' => $this->apartmentType?->name,
            'capacity_adults' => $this->capacity_adults,
            'capacity_children' => $this->capacity_children,
            'size' => $this->size,
            'beds_list' => $this->getBedsInfo(),
            'bathrooms' => $this->bathrooms,
            'facilities' => $this->whenLoaded('facilities', $this->facilities)
        ];
    }
}
