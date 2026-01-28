<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectImageResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'file_name' => $this->file_name,
            'url' => $this->url,
            'mime_type' => $this->mime_type,
            'file_size' => $this->file_size,
            'file_size_human' => $this->file_size_human,
            'caption' => $this->caption,
            'image_type' => $this->image_type,
            'image_type_display' => $this->image_type_display,
            'display_order' => $this->display_order,
            'uploaded_by' => $this->uploaded_by,
            'uploaded_at' => $this->created_at->toISOString(),
        ];
    }
}