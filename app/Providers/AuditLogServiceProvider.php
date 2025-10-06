<?php

namespace App\Providers;

use App\Interfaces\AuditLogRepositoryInterface;
use App\Repositories\AuditLogRepository;
use Illuminate\Support\ServiceProvider;

class AuditLogServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(
            AuditLogRepositoryInterface::class,
            AuditLogRepository::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
