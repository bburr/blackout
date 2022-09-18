<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Laravel\Jetstream\Http\Middleware\ShareInertiaData;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            $this->registerApiRoutes();
            $this->registerWebRoutes();
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            /** @phpstan-ignore-next-line  */
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }

    protected function registerApiRoutes(): void
    {
        // API routes currently disabled in prod
        if (\App::environment('production')) {
            return;
        }

        Route::middleware('api')
            ->prefix('api/v1')
            ->group(base_path('routes/api.php'));
    }

    protected function registerWebRoutes(): void
    {
        Route::middleware('web')
            ->withoutMiddleware(ShareInertiaData::class)
            ->group(base_path('routes/web.php'));
    }
}
