<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LguSectorResource extends JsonResource
{
    public function toArray($request): array
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'icon' => $this->icon,
            'color_code' => $this->color_code,
            'is_active' => $this->is_active,
            'display_order' => $this->display_order,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        // Include statistics if requested or if projects relation is loaded
        if ($request->query('include_stats') === 'true' || $this->relationLoaded('projects')) {
            $data['statistics'] = [
                'projects_count' => $this->projects_count,
                'approved_projects_count' => $this->approved_projects_count,
                'total_budget' => (float) $this->total_budget,
                'total_disbursed' => (float) $this->total_disbursed,
                'utilization_rate' => $this->utilization_rate,
            ];
        }

        return $data;
    }
}
