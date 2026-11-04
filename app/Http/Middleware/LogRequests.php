<?php

namespace App\Http\Middleware;

use App\Services\AuditLogService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogRequests
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Log only successful requests (status code 200-299)
        if ($response->isSuccessful()) {
            try {
                $user = $request->user();
                if ($user) {
                    $action = $this->getActionFromRequest($request);

                    AuditLogService::log(
                        $action,
                        null,
                        null,
                        ['request' => $request->all()]
                    );
                }
            } catch (\Exception $e) {
                Log::error('Request logging failed: ' . $e->getMessage());
            }
        }

        return $response;
    }

    protected function getActionFromRequest(Request $request)
    {
        $method = $request->method();
        $path = $request->path();

        $actionMap = [
            'GET' => 'viewed',
            'POST' => 'created',
            'PUT' => 'updated',
            'PATCH' => 'updated',
            'DELETE' => 'deleted',
        ];

        $action = $actionMap[$method] ?? 'accessed';

        // Add context to action
        $segments = explode('/', $path);
        $resource = end($segments);

        return $action . '_' . str_replace('-', '_', $resource);
    }
}
