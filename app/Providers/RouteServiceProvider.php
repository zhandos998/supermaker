<?php
namespace App\Providers;

use App\Http\Middleware\OptionalAuth;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Route::middleware('optional.auth', OptionalAuth::class);
    }
}
