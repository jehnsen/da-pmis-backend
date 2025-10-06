<?php

namespace App\Providers;

use App\Interfaces\NewsletterSubscriptionRepositoryInterface;
use App\Repositories\NewsletterSubscriptionRepository;
use Illuminate\Support\ServiceProvider;

class NewsletterSubscriptionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            NewsletterSubscriptionRepositoryInterface::class,
            NewsletterSubscriptionRepository::class
        );
    }

    public function boot(): void
    {
        //
    }
}
