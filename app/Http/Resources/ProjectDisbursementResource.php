<?php

namespace App\Http\Resources;

use App\Models\ProjectDisbursement;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectDisbursementResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'project' => $this->whenLoaded('project', function () {
                return [
                    'id' => $this->project->id,
                    'title' => $this->project->title,
                ];
            }),
            'amount' => (float) $this->amount,
            'category' => $this->category,
            'category_label' => ProjectDisbursement::CATEGORIES[$this->category] ?? $this->category,
            'description' => $this->description,
            'disbursement_date' => $this->disbursement_date?->format('Y-m-d'),
            'reference_number' => $this->reference_number,
            'vendor_name' => $this->vendor_name,
            'receipt_number' => $this->receipt_number,
            'notes' => $this->notes,
            'status' => $this->status,
            'status_label' => ucfirst($this->status),
            'created_by' => $this->whenLoaded('createdBy', function () {
                return [
                    'id' => $this->createdBy->id,
                    'name' => $this->createdBy->full_name ?? $this->createdBy->username,
                ];
            }),
            'approved_by' => $this->whenLoaded('approvedBy', function () {
                if (! $this->approvedBy) {
                    return null;
                }

                return [
                    'id' => $this->approvedBy->id,
                    'name' => $this->approvedBy->full_name ?? $this->approvedBy->username,
                ];
            }),
            'approved_at' => $this->approved_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
