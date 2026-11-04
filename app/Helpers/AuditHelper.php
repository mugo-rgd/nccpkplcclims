<?php

namespace App\Helpers;

use App\Services\AuditLogService;

class AuditHelper
{
    public static function log($action, $model = null, $oldValues = null, $newValues = null)
    {
        return AuditLogService::log($action, $model, $oldValues, $newValues);
    }
}
