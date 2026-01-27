<?php

namespace App\Providers;

use App\Interfaces\ProjectDisbursementRepositoryInterface;
use App\Repositories\ProjectDisbursementRepository;
use Illuminate\Support\ServiceProvider;

class ProjectDisbursementServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            ProjectDisbursementRepositoryInterface::class,
            ProjectDisbursementRepository::class
        );
    }

    public function boot(): void
    {
        //
    }
}
