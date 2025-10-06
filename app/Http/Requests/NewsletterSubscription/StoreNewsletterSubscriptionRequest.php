<?php

namespace App\Http\Requests\NewsletterSubscription;

use Illuminate\Foundation\Http\FormRequest;

class StoreNewsletterSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255', 'unique:newsletter_subscriptions,email'],
        ];
    }
}
