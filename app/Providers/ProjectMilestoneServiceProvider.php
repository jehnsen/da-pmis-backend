<?php

namespace App\Providers;

use App\Interfaces\ProjectMilestoneRepositoryInterface;
use App\Repositories\ProjectMilestoneRepository;
use Illuminate\Support\ServiceProvider;

class ProjectMilestoneServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            ProjectMilestoneRepositoryInterface::class,
            ProjectMilestoneRepository::class
        );
    }

    public function boot(): void
    {
        //
    }
}
