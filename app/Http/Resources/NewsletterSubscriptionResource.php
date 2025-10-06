<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class NewsletterSubscriptionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'is_active' => $this->is_active,
            'subscribed_at' => $this->subscribed_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
