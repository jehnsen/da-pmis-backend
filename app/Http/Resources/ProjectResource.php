<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray($request): array
    {
        $data = [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->when($this->shouldShowInternal(), $this->description, substr($this->description, 0, 200)),
            'department' => new DepartmentResource($this->whenLoaded('department')),
            'project_type' => $this->whenLoaded('projectType'),
            'project_status' => $this->whenLoaded('projectStatus'),
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'location' => $this->when($this->location_lat && $this->location_lng, [
                'lat' => $this->location_lat,
                'lng' => $this->location_lng,
            ]),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        // Include sensitive data only for internal view
        if ($this->shouldShowInternal()) {
            $data['budget'] = $this->budget;
            $data['team_members'] = ProjectTeamMemberResource::collection($this->whenLoaded('teamMembers'));
            $data['milestones'] = ProjectMilestoneResource::collection($this->whenLoaded('milestones'));
        }

        return $data;
    }

    private function shouldShowInternal(): bool
    {
        // Show internal data if user is authenticated or project is not public
        return auth()->check() || !$this->is_public;
    }
}
