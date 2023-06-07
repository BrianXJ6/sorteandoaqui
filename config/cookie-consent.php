<?php

use Illuminate\Support\Str;

return [
    /*
    |--------------------------------------------------------------------------
    | Cookie consent config
    |--------------------------------------------------------------------------
    */
    'enabled' => env('COOKIE_CONSENT_ENABLED', true),
    'cookie_name' => sprintf('%s_cookie_consent', Str::snake(config('app.name'))),
    'cookie_lifetime' => 365 * 20,
];
