<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;  // Correct import for ServiceProvider
use Illuminate\Routing\Route;
use Laravel\Passport\Http\Controllers\AccessTokenController;
use Laravel\Passport\Http\Controllers\AuthorizationController;
use Laravel\Passport\Http\Controllers\ApproveAuthorizationController;
use Laravel\Passport\Http\Controllers\DenyAuthorizationController;
use Laravel\Passport\Http\Controllers\TransientTokenController;
use Laravel\Passport\Http\Controllers\PersonalAccessTokenController;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->registerPolicies();

        // Define Passport routes manually
        Route::post('/oauth/token', [AccessTokenController::class, 'issueToken']);
        Route::get('/oauth/authorize', [AuthorizationController::class, 'authorize']);
        Route::post('/oauth/authorize', [ApproveAuthorizationController::class, 'approve']);
        Route::delete('/oauth/authorize', [DenyAuthorizationController::class, 'deny']);
        Route::post('/oauth/token/refresh', [TransientTokenController::class, 'refresh']);
        Route::post('/oauth/personal-access-tokens', [PersonalAccessTokenController::class, 'store']);

        // Optionally set token expiry
        Passport::tokensExpireIn(now()->addDays(15));
        Passport::refreshTokensExpireIn(now()->addDays(30));

    }
}
