<?php

namespace App\Providers;

use App\Interfaces\ContactInquiryRepositoryInterface;
use App\Repositories\ContactInquiryRepository;
use Illuminate\Support\ServiceProvider;

class ContactInquiryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            ContactInquiryRepositoryInterface::class,
            ContactInquiryRepository::class
        );
    }

    public function boot(): void
    {
        //
    }
}
