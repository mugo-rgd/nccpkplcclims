<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Active AI Provider
    |--------------------------------------------------------------------------
    |
    | Supported values:
    | - groq
    | - openai
    |
    */

    'provider' => env('AI_PROVIDER', 'groq'),

    /*
    |--------------------------------------------------------------------------
    | Shared Limits
    |--------------------------------------------------------------------------
    */

    'max_requests_per_minute' => (int) env(
        'AI_MAX_REQUESTS_PER_MINUTE',
        10
    ),

    'max_requests_per_day' => (int) env(
        'AI_MAX_REQUESTS_PER_DAY',
        200
    ),

    'max_input_characters' => (int) env(
        'AI_MAX_INPUT_CHARACTERS',
        10000
    ),

    'max_daily_tokens' => (int) env(
        'AI_MAX_DAILY_TOKENS',
        250000
    ),

    /*
    |--------------------------------------------------------------------------
    | Providers
    |--------------------------------------------------------------------------
    */

    'providers' => [
        'groq' => [
            'api_key' => env('GROQ_API_KEY'),

            'base_url' => env(
                'GROQ_BASE_URL',
                'https://api.groq.com/openai/v1'
            ),

            'model' => env(
                'GROQ_MODEL',
                'openai/gpt-oss-20b'
            ),

            'timeout' => (int) env(
                'GROQ_TIMEOUT',
                60
            ),
        ],

        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),

            'base_url' => env(
                'OPENAI_BASE_URL',
                'https://api.openai.com/v1'
            ),

            'model' => env('OPENAI_MODEL'),

            'timeout' => (int) env(
                'OPENAI_TIMEOUT',
                60
            ),
        ],
    ],
];
