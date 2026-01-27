<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'file_path' => $this->file_path,
            'file_name' => $this->file_name,
            'file_url' => $this->file_url, // Accessor from model
            'mime_type' => $this->mime_type,
            'file_size' => $this->file_size,
            'file_size_mb' => $this->file_size_mb, // Accessor from model
            'download_count' => $this->download_count ?? 0,
            'document_type' => $this->document_type,
            'category' => $this->whenLoaded('category', function () {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                    'slug' => $this->category->slug ?? null,
                ];
            }),
            'categories' => $this->whenLoaded('categories'),
            'department' => $this->whenLoaded('department', function () {
                return [
                    'id' => $this->department->id,
                    'name' => $this->department->name,
                ];
            }),
            'fiscal_year' => $this->fiscal_year,
            'is_featured' => $this->is_featured ?? false,
            'published_date' => $this->published_date?->format('Y-m-d'),
            'uploaded_by' => $this->whenLoaded('creator', function () {
                return [
                    'id' => $this->creator->id,
                    'name' => $this->creator->name,
                    'department' => $this->creator->department->name ?? null,
                ];
            }),
            'created_by' => new UserResource($this->whenLoaded('creator')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
