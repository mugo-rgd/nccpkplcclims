<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class QualityControllerMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        $allowedRoles = ['quality_controller', 'super_admin', 'admin', 'lab_manager'];

        if (!in_array($user->role, $allowedRoles)) {
            abort(403, 'Unauthorized. Quality Controller access required.');
        }

        return $next($request);
    }
}
