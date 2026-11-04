<?php

namespace App\Services\Ai;

use App\Contracts\Ai\AiProviderContract;
use App\Services\Ai\Providers\GroqProvider;
use App\Services\Ai\Providers\OpenAiProvider;
use RuntimeException;

class AiProviderManager
{
    public function provider(): AiProviderContract
    {
        return match (
            strtolower(
                trim(
                    (string) config(
                        'ai.provider',
                        'groq'
                    )
                )
            )
        ) {
            'groq' => app(GroqProvider::class),

            'openai' => app(OpenAiProvider::class),

            default => throw new RuntimeException(
                'The configured AI provider is not supported.'
            ),
        };
    }
}
