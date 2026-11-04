<?php

namespace App\Contracts\Ai;

interface AiProviderContract
{
    /**
     * Send a request to the configured AI provider.
     */
    public function request(array $payload): array;

    /**
     * Return the configured model.
     */
    public function model(): string;

    /**
     * Return the provider identifier.
     */
    public function name(): string;

    /**
     * Return the models available to the current API key.
     */
    public function availableModels(): array;

    /**
     * Determine whether the provider supports continuing a response
     * using previous_response_id.
     */
    public function supportsPreviousResponseId(): bool;
}
