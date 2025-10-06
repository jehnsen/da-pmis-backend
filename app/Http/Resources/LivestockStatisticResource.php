<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LivestockStatisticResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'region' => $this->whenLoaded('region'),
            'livestock_type' => $this->livestock_type,
            'population' => $this->population,
            'unit' => $this->unit,
            'recorded_date' => $this->recorded_date?->format('Y-m-d'),
            'fiscal_year' => $this->fiscal_year,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
