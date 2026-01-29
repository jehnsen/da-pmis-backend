<?php

namespace App\Providers;

use App\Models\ProjectImage;
use App\Observers\ProjectImageObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register model observers
        ProjectImage::observe(ProjectImageObserver::class);
    }
}
