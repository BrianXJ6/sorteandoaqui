<?php

namespace App\Providers;

use Illuminate\Support\Facades\{
    Route,
    RateLimiter,
};

use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->routes(function () {
            $domain = config('app.host');

            Route::domain($domain)
                ->middleware('api')
                ->prefix('api')
                ->name('api.')
                ->group(base_path('routes/api.php'));

            Route::domain($domain)
                ->middleware('web')
                ->name('web.')
                ->group(base_path('routes/web.php'));
        });

        // Rate limit for web routes
        RateLimiter::for('web', function (Request $request) {
            return $request->user()
                ? Limit::perMinute(120)->by($request->user()->id)
                : Limit::perMinute(60)->by($request->ip());
        });

        // Rate limit for api routes
        RateLimiter::for('api', function (Request $request) {
            return $request->user()
                ? Limit::perMinute(240)->by($request->user()->id)
                : Limit::perMinute(120)->by($request->ip());
        });
    }
}
