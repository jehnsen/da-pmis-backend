<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProgressReportResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'project' => $this->whenLoaded('project', function () {
                return [
                    'id' => $this->project->id,
                    'title' => $this->project->title,
                ];
            }),
            'department' => new DepartmentResource($this->whenLoaded('department')),
            'report_period' => $this->report_period,
            'reporting_date' => $this->reporting_date?->format('Y-m-d'),

            // Progress percentages
            'physical_progress' => $this->physical_progress,
            'financial_progress' => $this->financial_progress,
            'variance' => $this->variance,

            // Progress status
            'progress_status' => $this->progress_status,
            'progress_status_display' => $this->progress_status_display,

            // Narrative fields
            'summary' => $this->summary,
            'accomplishments' => $this->accomplishments,
            'issues' => $this->issues,
            'risks' => $this->risks,
            'next_steps' => $this->next_steps,

            // Milestones
            'milestones_completed' => $this->milestones_completed,
            'milestones_completed_count' => $this->completed_milestones_count,

            // Metrics (existing functionality)
            'metrics' => ReportMetricResource::collection($this->whenLoaded('metrics')),

            // Audit fields
            'created_by' => $this->whenLoaded('creator', function () {
                return [
                    'id' => $this->creator->id,
                    'name' => $this->creator->full_name ?? $this->creator->username,
                ];
            }),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
