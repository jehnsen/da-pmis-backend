<?php

namespace App\Providers;

use App\Interfaces\CropProductionRepositoryInterface;
use App\Repositories\CropProductionRepository;
use Illuminate\Support\ServiceProvider;

class CropProductionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            CropProductionRepositoryInterface::class,
            CropProductionRepository::class
        );
    }

    public function boot(): void
    {
        //
    }
}
