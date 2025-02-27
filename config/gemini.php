<?php

return [

    /*
    |--------------------------------------------------------------------------
    | GEMINI Configuration
    |--------------------------------------------------------------------------
    |
    | For configuring GEMINI related settings
    |
    */
    'api_key' => env('GEMINI_API_KEY'),
    'model' => env('GEMINI_MODEL') ?? 'gemini-2.0-flash-001',
];
