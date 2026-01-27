<?php

namespace App\Providers;

use App\Interfaces\DepartmentReportRepositoryInterface;
use App\Repositories\DepartmentReportRepository;
use Illuminate\Support\ServiceProvider;

class DepartmentReportServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            DepartmentReportRepositoryInterface::class,
            DepartmentReportRepository::class
        );
    }

    public function boot(): void
    {
        //
    }
}
