<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'           => $this->id,
            'email'        => $this->email,
            'full_name'    => $this->full_name,
            'username'     => $this->username,
            'school_id'    => $this->school_id,
            'role_id'      => $this->role_id,
            'department_id'=> $this->department_id,
            'created_at'   => $this->created_at?->toDateTimeString(),
            'updated_at'   => $this->updated_at?->toDateTimeString(),
        ];
    }
}
