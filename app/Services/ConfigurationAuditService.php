<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Throwable;

class ConfigurationAuditService
{
    public function log(
        string $action,
        Model $subject,
        string $description,
        array $oldValues = [],
        array $newValues = []
    ): void {
        try {
            ActivityLog::query()->create([
                'user_id' => Auth::id(),

                'action' => $action,

                'module' => 'laboratory_configuration',

                'description' => $description,

                'subject_type' => $subject::class,

                'subject_id' => $subject->getKey(),

                'properties' => [
                    'old' => $oldValues,
                    'new' => $newValues,
                ],

                'ip_address' => Request::ip(),

                'user_agent' => mb_substr(
                    (string) Request::userAgent(),
                    0,
                    255
                ),
            ]);
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
