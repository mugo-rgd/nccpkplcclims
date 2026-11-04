<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait LogsActivity
{
    protected static function bootLogsActivity()
    {
        static::created(function ($model) {
            $model->logActivity('created');
        });

        static::updated(function ($model) {
            $model->logActivity('updated');
        });

        static::deleted(function ($model) {
            $model->logActivity('deleted');
        });
    }

    protected function logActivity($action)
    {
        try {
            $oldValues = null;
            $newValues = null;

            if ($action === 'updated') {
                $oldValues = $this->getOriginal();
                $newValues = $this->getAttributes();

                // Remove sensitive data
                unset($oldValues['password'], $oldValues['remember_token']);
                unset($newValues['password'], $newValues['remember_token']);

                // Only log if there are actual changes
                $changes = array_diff_assoc($newValues, $oldValues);
                if (empty($changes)) {
                    return;
                }
            } elseif ($action === 'created') {
                $newValues = $this->getAttributes();
                unset($newValues['password'], $newValues['remember_token']);
            } elseif ($action === 'deleted') {
                $oldValues = $this->getOriginal();
                unset($oldValues['password'], $oldValues['remember_token']);
            }

            // Use the correct column names
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => $action,
                'model_type' => get_class($this),  // Changed from 'model' to 'model_type'
                'model_id' => $this->getKey(),
                'old_values' => $oldValues,        // Changed from 'changes' to 'old_values'
                'new_values' => $newValues,        // Changed from 'changes' to 'new_values'
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'url' => Request::fullUrl(),
                'method' => Request::method(),
            ]);
        } catch (\Exception $e) {
            // Log error but don't break the application
            \Log::error('Failed to create audit log: ' . $e->getMessage());
        }
    }
}
