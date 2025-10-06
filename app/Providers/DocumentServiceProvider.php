<?php

namespace App\Providers;

use App\Interfaces\DocumentRepositoryInterface;
use App\Repositories\DocumentRepository;
use Illuminate\Support\ServiceProvider;

class DocumentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            DocumentRepositoryInterface::class,
            DocumentRepository::class
        );
    }

    public function boot(): void
    {
        //
    }
}
