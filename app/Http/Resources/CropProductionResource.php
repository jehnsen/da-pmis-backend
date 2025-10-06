<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CropProductionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'region' => $this->whenLoaded('region'),
            'crop_name' => $this->crop_name,
            'production_volume' => $this->production_volume,
            'unit' => $this->unit,
            'harvest_date' => $this->harvest_date?->format('Y-m-d'),
            'fiscal_year' => $this->fiscal_year,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
