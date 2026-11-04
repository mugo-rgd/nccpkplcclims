<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogService
{
    public static function log($action, $model = null, $oldValues = null, $newValues = null)
    {
        try {
            $logData = [
                'user_id' => Auth::id(),
                'action' => $action,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'url' => Request::fullUrl(),
                'method' => Request::method(),
            ];

            if ($model) {
                $logData['model_type'] = get_class($model);
                $logData['model_id'] = $model->getKey();

                // Remove sensitive data
                if ($oldValues) {
                    unset($oldValues['password'], $oldValues['remember_token']);
                }
                if ($newValues) {
                    unset($newValues['password'], $newValues['remember_token']);
                }

                $logData['old_values'] = $oldValues;
                $logData['new_values'] = $newValues;
            }

            return AuditLog::create($logData);
        } catch (\Exception $e) {
            // Log error but don't break the application
            \Log::error('Failed to create audit log: ' . $e->getMessage());
            return null;
        }
    }
}
