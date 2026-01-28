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
            'description' => $this->when($this->shouldShowInternal(), $this->description, substr($this->description ?? '', 0, 200)),
            'department' => new DepartmentResource($this->whenLoaded('department')),
            'project_type' => $this->whenLoaded('projectType'),
            'project_status' => $this->whenLoaded('projectStatus'),
            'budget' => $this->budget,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'location' => $this->when($this->location_lat && $this->location_lng, [
                'lat' => $this->location_lat,
                'lng' => $this->location_lng,
            ]),
            'images' => ProjectImageResource::collection($this->whenLoaded('images')),
            'cover_image' => new ProjectImageResource($this->images()->cover()->first()),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        // Include sensitive data only for internal view
        if ($this->shouldShowInternal()) {
            $data['budget'] = $this->budget;
            $data['team_members'] = ProjectTeamMemberResource::collection($this->whenLoaded('teamMembers'));
            $data['milestones'] = ProjectMilestoneResource::collection($this->whenLoaded('milestones'));
            $data['documents'] = DocumentResource::collection($this->whenLoaded('documents'));
            $data['audit_logs'] = AuditLogResource::collection($this->whenLoaded('auditLogs'));

            // Approval workflow data
            $data['approval_status'] = $this->approval_status;
            $data['approval_status_display'] = $this->approval_status_display;
            $data['submitted_by'] = $this->whenLoaded('submitter', function () {
                return [
                    'id' => $this->submitter->id,
                    'name' => $this->submitter->full_name ?? $this->submitter->username,
                ];
            });
            $data['submitted_at'] = $this->submitted_at?->toIso8601String();
            $data['approvals'] = ProjectApprovalResource::collection($this->whenLoaded('approvals'));
        }

        return $data;
    }

    private function shouldShowInternal(): bool
    {
        // Show internal data if user is authenticated (via any guard) or project is not public
        // Check all possible auth guards (sanctum for API tokens, web for sessions)
        return auth()->guard('sanctum')->check()
            || auth()->guard('web')->check()
            || ! $this->is_public;
    }
}
