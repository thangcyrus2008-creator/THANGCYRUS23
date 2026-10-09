<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            $enableAdmin = filter_var(env('ENABLE_ADMIN_PANEL', false), FILTER_VALIDATE_BOOLEAN);
            if ($enableAdmin && file_exists(base_path('routes/admin.php'))) {
                Route::middleware('web')
                    ->group(base_path('routes/admin.php'));
            } else {
                Route::middleware('web')->group(function () {
                    Route::get('/admin', function () {
                        return redirect('http://127.0.0.1:8000/admin');
                    })->name('admin.index');

                    Route::get('/admin/websites/settings', function () {
                        return redirect('http://127.0.0.1:8000/admin/websites/settings');
                    })->name('admin.websites.settings');

                    Route::any('/admin/{any?}', function () {
                        return redirect('http://127.0.0.1:8000/admin');
                    })->where('any', '.*');
                });
            }

            Route::middleware('web')
                ->group(base_path('routes/member.php'));
        });
    }
}
