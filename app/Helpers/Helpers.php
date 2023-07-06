<?php

use Illuminate\Support\Str;

if (!function_exists('generateTitle')) {
    /**
     * Generate title for pages.
     *
     * @param string $label
     *
     * @return string
     */
    function generateTitle(string $label): string
    {
        return sprintf('%s - %s', config('app.name'), $label);
    }
}

if (!function_exists('onlyNumbers')) {
    /**
     * Prepare field to listen regex.
     *
     * @param null|string $value
     *
     * @return string
     */
    function onlyNumbers(?string $value = null): string
    {
        return Str::of($value)->replaceMatches('/[^0-9]+/', '')->value();
    }
}
