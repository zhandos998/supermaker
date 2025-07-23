<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\TelescopeApplicationServiceProvider;
use Illuminate\Support\Facades\Route;

class TelescopeServiceProvider extends TelescopeApplicationServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Telescope::night();

        $this->hideSensitiveRequestDetails();

        $isLocal = $this->app->environment('local');

        Telescope::filter(function (IncomingEntry $entry) use ($isLocal) {
            return $isLocal ||
                   $entry->isReportableException() ||
                   $entry->isFailedRequest() ||
                   $entry->isFailedJob() ||
                   $entry->isScheduledTask() ||
                   $entry->hasMonitoredTag();
        });

        Telescope::tag(function (IncomingEntry $entry) {
            if ($entry->type === 'request') {
                $tags = [
                    'status:' . $entry->content['response_status'],
                ];

                $path = parse_url($entry->content['uri'], PHP_URL_PATH);

                if (str_starts_with($path, '/api/')) {
                    $tags[] = 'path:api';
                } else {
                    $tags[] = 'path:web';
                }

                // Имя контроллера
                $route = Route::getRoutes()->match(request());
                $action = $route->getActionName(); // App\Http\Controllers\Api\UserController@index

                if ($action && $action !== 'Closure') {
                    $controller = explode('@', class_basename($action))[0] ?? null; // UserController
                    if ($controller) {
                        $tags[] = 'controller:' . $controller;
                    }
                } else {
                    $tags[] = 'controller:unknown';
                }

                return $tags;
            }

            return [];
        });
    }

    /**
     * Prevent sensitive request details from being logged by Telescope.
     */
    protected function hideSensitiveRequestDetails(): void
    {
        if ($this->app->environment('local')) {
            return;
        }

        Telescope::hideRequestParameters(['_token']);

        Telescope::hideRequestHeaders([
            'cookie',
            'x-csrf-token',
            'x-xsrf-token',
        ]);
    }

    /**
     * Register the Telescope gate.
     *
     * This gate determines who can access Telescope in non-local environments.
     */
    protected function gate(): void
    {
        Gate::define('viewTelescope', function ($user) {
            return true;

//            return in_array($user->email, [
//                //
//            ]);
        });
    }
}
