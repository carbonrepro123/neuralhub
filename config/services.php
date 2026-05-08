<?php

return [
    'daily' => [
        'key' => env('DAILY_API_KEY'),
        'base_url' => env('DAILY_BASE_URL', 'https://api.daily.co/v1'),
        'domain' => env('DAILY_DOMAIN'),
    ],

    'jitsi' => [
        'base_url' => env('JITSI_BASE_URL', 'https://meet.jit.si'),
    ],

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
        'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
    ],
];
