<?php

namespace App\Providers;

use App\Interfaces\NewsUpdateRepositoryInterface;
use App\Repositories\NewsUpdateRepository;
use Illuminate\Support\ServiceProvider;

class NewsUpdateServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            NewsUpdateRepositoryInterface::class,
            NewsUpdateRepository::class
        );
    }

    public function boot(): void
    {
        //
    }
}
