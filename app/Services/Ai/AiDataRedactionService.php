<?php

namespace App\Services\Ai;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class AiDataRedactionService
{
    /**
     * Fields that must never be sent to the AI API.
     */
    private const BLOCKED_KEYS = [
        'password',
        'password_confirmation',
        'remember_token',
        'password_reset_token',
        'session_id',
        'api_key',
        'access_token',
        'refresh_token',
        'secret',
        'signature_path',
        'signature_filename',
    ];

    /**
     * Recursively remove sensitive values from tool results.
     */
    public function redact(array $data): array
    {
        return $this->redactValue($data);
    }

    private function redactValue(mixed $value): mixed
    {
        if ($value instanceof Collection) {
            $value = $value->all();
        }

        if (!is_array($value)) {
            return $this->redactString($value);
        }

        $clean = [];

        foreach ($value as $key => $item) {
            $normalizedKey = strtolower((string) $key);

            if (in_array($normalizedKey, self::BLOCKED_KEYS, true)) {
                continue;
            }

            $clean[$key] = $this->redactValue($item);
        }

        return $clean;
    }

    private function redactString(mixed $value): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        /*
         * Mask likely credentials and authorization headers.
         */
        $value = preg_replace(
            '/Bearer\s+[A-Za-z0-9\-\._~\+\/]+=*/i',
            'Bearer [REDACTED]',
            $value
        );

        $value = preg_replace(
            '/sk-[A-Za-z0-9_-]{15,}/i',
            '[REDACTED_API_KEY]',
            $value
        );

        /*
         * Mask long token-like values.
         */
        $value = preg_replace(
            '/\b[A-Fa-f0-9]{40,}\b/',
            '[REDACTED_TOKEN]',
            $value
        );

        return $value;
    }
}
