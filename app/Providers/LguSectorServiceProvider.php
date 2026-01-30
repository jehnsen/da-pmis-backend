<?php

namespace App\Providers;

use App\Interfaces\LguSectorRepositoryInterface;
use App\Repositories\LguSectorRepository;
use Illuminate\Support\ServiceProvider;

class LguSectorServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(LguSectorRepositoryInterface::class, LguSectorRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
