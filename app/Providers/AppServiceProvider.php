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
use Illuminate\Support\Facades\URL;
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
        |--------------------------------------------------------------------------
        | Force HTTPS URL Generation in Production
        |--------------------------------------------------------------------------
        |
        | The production application runs behind Coolify/Traefik
        | and Nginx.
        |
        | The public website uses HTTPS, but Laravel may detect
        | the internal proxy connection as HTTP.
        |
        | This can generate insecure URLs for:
        | - CSS and JavaScript assets
        | - Application routes
        | - AJAX requests
        |
        | Browsers block these HTTP requests when the page
        | itself is loaded over HTTPS (Mixed Content).
        |
        | Force Laravel to generate HTTPS URLs in production.
        | Local development remains unchanged.
        |
        */

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        /*
        |--------------------------------------------------------------------------
        | Bootstrap 5 Pagination
        |--------------------------------------------------------------------------
        */

        Paginator::useBootstrapFive();

        /*
        |--------------------------------------------------------------------------
        | Certificate Policy
        |--------------------------------------------------------------------------
        */

        Gate::policy(
            Certificate::class,
            CertificatePolicy::class
        );

        /*
        |--------------------------------------------------------------------------
        | Spatie Permission Middleware Aliases
        |--------------------------------------------------------------------------
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
        |--------------------------------------------------------------------------
        | API Rate Limiting
        |--------------------------------------------------------------------------
        |
        | Limit requests to 60 per minute per authenticated user.
        | For unauthenticated requests, use the client IP.
        |
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
