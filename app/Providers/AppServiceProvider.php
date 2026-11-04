<?php

namespace App\Providers;

use App\Models\Certificate;
use App\Policies\CertificatePolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Model policies.
     *
     * @var array<class-string, class-string>
     */
    protected array $policies = [
        Certificate::class => CertificatePolicy::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap application services.
     */
    public function boot(Router $router): void
    {
        /*
         * Use Bootstrap 5 pagination globally.
         */
        Paginator::useBootstrapFive();

        /*
         * Register certificate policy.
         */
        Gate::policy(
            Certificate::class,
            CertificatePolicy::class
        );

        /*
         * Register Spatie permission middleware aliases.
         */
        $router->aliasMiddleware(
            'role',
            RoleMiddleware::class
        );

        $router->aliasMiddleware(
            'permission',
            PermissionMiddleware::class
        );

        $router->aliasMiddleware(
            'role_or_permission',
            RoleOrPermissionMiddleware::class
        );

        /*
         * Rate limiter.
         *
         * Keep or adjust this only if you already use
         * the "api" limiter in your application.
         */
        RateLimiter::for(
            'api',
            function (Request $request) {
                return Limit::perMinute(60)
                    ->by(
                        $request->user()?->id
                        ?: $request->ip()
                    );
            }
        );
    }
}
