<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReportMetricResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'metric_name' => $this->metric_name,
            'metric_value' => $this->metric_value,
            'previous_value' => $this->previous_value,
            'change_percentage' => $this->previous_value > 0
                ? round((($this->metric_value - $this->previous_value) / $this->previous_value) * 100, 2)
                : null,
            'created_at' => $this->created_at,
        ];
    }
}
