<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;

class LogUserActivity
{
    public function handle(Request $request, Closure $next)
    {
        // Log the request if user is authenticated
        if (auth()->check()) {
            // Exclude certain routes from logging (optional)
            $excludeRoutes = ['login', 'logout', 'register', 'password.*'];
            $currentRoute = $request->route()->getName() ?? '';

            $shouldLog = true;
            foreach ($excludeRoutes as $route) {
                if (str_contains($currentRoute, $route)) {
                    $shouldLog = false;
                    break;
                }
            }

            if ($shouldLog && !$request->ajax()) {
                AuditLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'view',
                    'model' => $request->route()->getName() ?? $request->path(),
                    'model_id' => null,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                ]);
            }
        }

        return $next($request);
    }
}
