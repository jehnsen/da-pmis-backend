<?php

namespace App\Providers;

use App\Interfaces\ProjectRepositoryInterface;
use App\Repositories\ProjectRepository;
use Illuminate\Support\ServiceProvider;

class ProjectServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            ProjectRepositoryInterface::class,
            ProjectRepository::class
        );
    }

    public function boot(): void
    {
        //
    }
}
