<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProgressReportResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'department' => new DepartmentResource($this->whenLoaded('department')),
            'report_period' => $this->report_period,
            'report_date' => $this->report_date?->format('Y-m-d'),
            'summary' => $this->summary,
            'metrics' => ReportMetricResource::collection($this->whenLoaded('metrics')),
            'created_by' => new UserResource($this->whenLoaded('creator')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
