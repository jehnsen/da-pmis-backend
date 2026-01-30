<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'username' => $this->username,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'role' => $this->whenLoaded('role', function () {
                return [
                    'id' => $this->role->id,
                    'name' => $this->role->name,
                    'display_name' => ucwords(str_replace('_', ' ', $this->role->name)),
                ];
            }),
            'role_id' => $this->role_id,
            'department' => $this->whenLoaded('department', function () {
                return [
                    'id' => $this->department->id,
                    'name' => $this->department->name,
                ];
            }),
            'department_id' => $this->department_id,
            'municipality' => $this->whenLoaded('municipality', function () {
                return [
                    'id' => $this->municipality->id,
                    'name' => $this->municipality->name,
                    'type' => $this->municipality->type,
                    'type_display' => $this->municipality->type_display,
                    'province' => $this->municipality->province ? [
                        'id' => $this->municipality->province->id,
                        'name' => $this->municipality->province->name,
                        'region' => $this->municipality->province->region ? [
                            'id' => $this->municipality->province->region->id,
                            'name' => $this->municipality->province->region->name,
                            'code' => $this->municipality->province->region->code,
                        ] : null,
                    ] : null,
                ];
            }),
            'municipality_id' => $this->municipality_id,
            'is_active' => $this->is_active ?? true,
            'last_login_at' => $this->last_login_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
