<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the CORS settings for your Laravel backend.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'storage/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['*'],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => ['Authorization', 'Content-Disposition'],
    'max_age' => 0,
    'supports_credentials' => true,



];
