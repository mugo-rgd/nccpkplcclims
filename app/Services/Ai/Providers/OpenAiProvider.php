<?php

namespace App\Services\Ai\Providers;

use App\Contracts\Ai\AiProviderContract;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAiProvider implements AiProviderContract
{
    /**
     * Send a Responses API request to OpenAI.
     */
    public function request(array $payload): array
    {
        $apiKey = $this->apiKey();

        /*
         * Always use the model configured for OpenAI.
         */
        $payload['model'] = $this->model();

        try {
            $response = Http::withToken($apiKey)
                ->acceptJson()
                ->asJson()
                ->timeout($this->timeout())
                ->post(
                    $this->baseUrl() . '/responses',
                    $payload
                );
        } catch (ConnectionException $exception) {
            throw new RuntimeException(
                'The OpenAI service could not be reached.',
                previous: $exception
            );
        }

        if ($response->failed()) {
            $this->throwProviderException(
                $response->status(),
                $response->json(),
                $response->body()
            );
        }

        $result = $response->json();

        if (!is_array($result)) {
            throw new RuntimeException(
                'OpenAI returned an invalid JSON response.'
            );
        }

        return $result;
    }

    /**
     * Return models available to the current OpenAI API key.
     */
    public function availableModels(): array
    {
        $apiKey = $this->apiKey();

        try {
            $response = Http::withToken($apiKey)
                ->acceptJson()
                ->timeout(30)
                ->get(
                    $this->baseUrl() . '/models'
                );
        } catch (ConnectionException $exception) {
            throw new RuntimeException(
                'The OpenAI model list could not be retrieved.',
                previous: $exception
            );
        }

        if ($response->failed()) {
            $this->throwProviderException(
                $response->status(),
                $response->json(),
                $response->body()
            );
        }

        return collect(
            $response->json('data', [])
        )
            ->pluck('id')
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    /**
     * Return the configured OpenAI model.
     */
    public function model(): string
    {
        $model = trim(
            (string) config(
                'ai.providers.openai.model'
            )
        );

        if ($model === '') {
            throw new RuntimeException(
                'The OpenAI model is not configured.'
            );
        }

        return $model;
    }

    /**
     * Return the provider identifier.
     */
    public function name(): string
    {
        return 'openai';
    }

    /**
     * OpenAI Responses API supports previous_response_id.
     */
    public function supportsPreviousResponseId(): bool
    {
        return true;
    }

    /**
     * Return the configured API key.
     */
    private function apiKey(): string
    {
        $apiKey = trim(
            (string) config(
                'ai.providers.openai.api_key'
            )
        );

        if ($apiKey === '') {
            throw new RuntimeException(
                'The OpenAI API key is not configured.'
            );
        }

        return $apiKey;
    }

    /**
     * Return the OpenAI base URL.
     */
    private function baseUrl(): string
    {
        return rtrim(
            (string) config(
                'ai.providers.openai.base_url',
                'https://api.openai.com/v1'
            ),
            '/'
        );
    }

    /**
     * Return the request timeout.
     */
    private function timeout(): int
    {
        return max(
            (int) config(
                'ai.providers.openai.timeout',
                60
            ),
            1
        );
    }

    /**
     * Convert OpenAI API failures into controlled exceptions.
     */
    private function throwProviderException(
        int $status,
        mixed $json,
        string $body
    ): never {
        $message = is_array($json)
            ? data_get($json, 'error.message')
            : null;

        $message = trim(
            (string) ($message ?: $body)
        );

        $errorCode = is_array($json)
            ? data_get($json, 'error.code')
            : null;

        if ($status === 400) {
            throw new RuntimeException(
                'OpenAI rejected the AI request: ' . $message
            );
        }

        if ($status === 401) {
            throw new RuntimeException(
                'The configured OpenAI API key is invalid.'
            );
        }

        if ($status === 403) {
            throw new RuntimeException(
                'The OpenAI project denied access to the configured model.'
            );
        }

        if ($status === 404) {
            throw new RuntimeException(
                'The configured OpenAI model or endpoint was not found.'
            );
        }

        if (
            $status === 429
            && (
                $errorCode === 'insufficient_quota'
                || str_contains(
                    strtolower($message),
                    'quota'
                )
            )
        ) {
            throw new RuntimeException(
                'The OpenAI API project has no available usage credit.'
            );
        }

        if ($status === 429) {
            throw new RuntimeException(
                'The OpenAI request limit has been reached. Please wait and try again.'
            );
        }

        if ($status >= 500) {
            throw new RuntimeException(
                'The OpenAI service is temporarily unavailable.'
            );
        }

        throw new RuntimeException(
            "OpenAI API request failed with HTTP {$status}: {$message}"
        );
    }
}
