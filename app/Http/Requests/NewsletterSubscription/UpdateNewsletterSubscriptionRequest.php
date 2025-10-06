<?php

namespace App\Http\Requests\NewsletterSubscription;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNewsletterSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'is_active' => ['required', 'boolean'],
        ];
    }
}
