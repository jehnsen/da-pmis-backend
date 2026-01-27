<?php

namespace App\Providers;

use App\Interfaces\UserManagementRepositoryInterface;
use App\Repositories\UserManagementRepository;
use Illuminate\Support\ServiceProvider;

class UserManagementServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            UserManagementRepositoryInterface::class,
            UserManagementRepository::class
        );
    }

    public function boot(): void
    {
        //
    }
}
