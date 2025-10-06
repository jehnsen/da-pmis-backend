<?php

namespace App\Providers;

use App\Interfaces\LivestockStatisticRepositoryInterface;
use App\Repositories\LivestockStatisticRepository;
use Illuminate\Support\ServiceProvider;

class LivestockStatisticServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            LivestockStatisticRepositoryInterface::class,
            LivestockStatisticRepository::class
        );
    }

    public function boot(): void
    {
        //
    }
}
