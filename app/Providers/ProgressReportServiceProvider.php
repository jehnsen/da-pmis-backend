<?php

namespace App\Providers;

use App\Interfaces\ProgressReportRepositoryInterface;
use App\Repositories\ProgressReportRepository;
use Illuminate\Support\ServiceProvider;

class ProgressReportServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            ProgressReportRepositoryInterface::class,
            ProgressReportRepository::class
        );
    }

    public function boot(): void
    {
        //
    }
}
