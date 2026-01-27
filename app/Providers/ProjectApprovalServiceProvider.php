<?php

namespace App\Providers;

use App\Interfaces\ProjectApprovalRepositoryInterface;
use App\Repositories\ProjectApprovalRepository;
use Illuminate\Support\ServiceProvider;

class ProjectApprovalServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            ProjectApprovalRepositoryInterface::class,
            ProjectApprovalRepository::class
        );
    }

    public function boot(): void
    {
        //
    }
}
