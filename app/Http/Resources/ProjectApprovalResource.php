<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectApprovalResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'user_id' => $this->user_id,
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->full_name ?? $this->user->username,
                    'email' => $this->user->email,
                ];
            }),
            'action' => $this->action,
            'action_display' => $this->action_display,
            'level' => $this->level,
            'level_display' => $this->level_display,
            'comments' => $this->comments,
            'reason' => $this->reason,
            'from_status' => $this->from_status,
            'to_status' => $this->to_status,
            'action_taken_at' => $this->action_taken_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
