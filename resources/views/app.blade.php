<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-100">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta http-equiv="Content-Type" content="text/html" />
        <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no, shrink-to-fit=no">
        <meta name="robots" content="index, follow" />
        <meta name="googlebot" content="INDEX, FOLLOW" />
        @includeIf('favicons')
        @vite('resources/sass/app.scss')
        @inertiaHead
        @routes
    </head>
    <body class="h-100">
        @inertia
        @vite('resources/js/app.js')
    </body>
</html>
