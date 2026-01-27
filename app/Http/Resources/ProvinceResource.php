<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProvinceResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'region_id' => $this->region_id,
            'code' => $this->code,
            'name' => $this->name,
            'psgc_code' => $this->psgc_code,
            'coordinates' => $this->when($this->latitude && $this->longitude, [
                'latitude' => (float) $this->latitude,
                'longitude' => (float) $this->longitude,
            ]),
            'is_active' => $this->is_active,
            'region' => $this->whenLoaded('region', function () {
                return [
                    'id' => $this->region->id,
                    'code' => $this->region->code,
                    'name' => $this->region->name,
                ];
            }),
            'municipality_count' => $this->when($this->relationLoaded('municipalities'), $this->municipalities->count()),
            'municipalities' => MunicipalityResource::collection($this->whenLoaded('municipalities')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
