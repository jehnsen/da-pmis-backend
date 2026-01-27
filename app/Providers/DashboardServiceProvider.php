<?php

namespace App\Providers;

use App\Interfaces\DashboardRepositoryInterface;
use App\Repositories\DashboardRepository;
use Illuminate\Support\ServiceProvider;

class DashboardServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            DashboardRepositoryInterface::class,
            DashboardRepository::class
        );
    }

    public function boot(): void
    {
        //
    }
}
