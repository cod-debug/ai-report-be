<?php

return [

    /*
    |--------------------------------------------------------------------------
    | OpenAI Configuration
    |--------------------------------------------------------------------------
    |
    | For configuring OpenAI related settings
    |
    */
    'openai_key' => env('OPENAI_KEY'),
    'openai_api_model' => env('OPENAI_API_MODEL', 'gpt-4o-mini'),
];
