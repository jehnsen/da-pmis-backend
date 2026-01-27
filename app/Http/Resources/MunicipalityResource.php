<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MunicipalityResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'province_id' => $this->province_id,
            'code' => $this->code,
            'name' => $this->name,
            'psgc_code' => $this->psgc_code,
            'type' => $this->type,
            'type_display' => $this->type_display,
            'coordinates' => $this->when($this->latitude && $this->longitude, [
                'latitude' => (float) $this->latitude,
                'longitude' => (float) $this->longitude,
            ]),
            'zip_code' => $this->zip_code,
            'is_active' => $this->is_active,
            'is_city' => $this->isCity(),
            'province' => $this->whenLoaded('province', function () {
                return [
                    'id' => $this->province->id,
                    'code' => $this->province->code,
                    'name' => $this->province->name,
                ];
            }),
            'full_location' => $this->when($this->relationLoaded('province'), $this->full_location),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
