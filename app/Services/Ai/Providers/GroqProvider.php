<?php

namespace App\Services\Ai\Providers;

use App\Contracts\Ai\AiProviderContract;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GroqProvider implements AiProviderContract
{
    /**
     * Send a Responses API request to Groq.
     */
    public function request(array $payload): array
    {
        $apiKey = $this->apiKey();

        /*
         * Groq currently does not support previous_response_id.
         * Remove it defensively in case another service includes it.
         */
        unset($payload['previous_response_id']);

        /*
         * Always use the model configured for Groq.
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
                'The Groq AI service could not be reached.',
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
                'Groq returned an invalid JSON response.'
            );
        }

        return $result;
    }

    /**
     * Return models available to the current Groq API key.
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
                'The Groq model list could not be retrieved.',
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
     * Return the configured Groq model.
     */
    public function model(): string
    {
        $model = trim(
            (string) config(
                'ai.providers.groq.model'
            )
        );

        if ($model === '') {
            throw new RuntimeException(
                'The Groq model is not configured.'
            );
        }

        return $model;
    }

    /**
     * Return the provider identifier.
     */
    public function name(): string
    {
        return 'groq';
    }

    /**
     * Groq requires conversation history to be supplied manually.
     */
    public function supportsPreviousResponseId(): bool
    {
        return false;
    }

    /**
     * Return the configured API key.
     */
    private function apiKey(): string
    {
        $apiKey = trim(
            (string) config(
                'ai.providers.groq.api_key'
            )
        );

        if ($apiKey === '') {
            throw new RuntimeException(
                'The Groq API key is not configured.'
            );
        }

        return $apiKey;
    }

    /**
     * Return the Groq base URL.
     */
    private function baseUrl(): string
    {
        return rtrim(
            (string) config(
                'ai.providers.groq.base_url',
                'https://api.groq.com/openai/v1'
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
                'ai.providers.groq.timeout',
                60
            ),
            1
        );
    }

    /**
     * Convert Groq API failures into controlled exceptions.
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

        if ($status === 400) {
            throw new RuntimeException(
                'Groq rejected the AI request: ' . $message
            );
        }

        if ($status === 401) {
            throw new RuntimeException(
                'The configured Groq API key is invalid.'
            );
        }

        if ($status === 403) {
            throw new RuntimeException(
                'The Groq project denied access to the requested model.'
            );
        }

        if ($status === 404) {
            throw new RuntimeException(
                'The configured Groq model or endpoint was not found.'
            );
        }

        if ($status === 429) {
            throw new RuntimeException(
                'The Groq request limit has been reached. Please wait and try again.'
            );
        }

        if ($status >= 500) {
            throw new RuntimeException(
                'The Groq AI service is temporarily unavailable.'
            );
        }

        throw new RuntimeException(
            "Groq API request failed with HTTP {$status}: {$message}"
        );
    }
}
