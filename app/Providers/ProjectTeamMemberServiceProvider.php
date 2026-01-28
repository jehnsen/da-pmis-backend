<?php

namespace App\Providers;

use App\Interfaces\ProjectTeamMemberRepositoryInterface;
use App\Repositories\ProjectTeamMemberRepository;
use Illuminate\Support\ServiceProvider;

class ProjectTeamMemberServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            ProjectTeamMemberRepositoryInterface::class,
            ProjectTeamMemberRepository::class
        );
    }

    public function boot(): void
    {
        //
    }
}
