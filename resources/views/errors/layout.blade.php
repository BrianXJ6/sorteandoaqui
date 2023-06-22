<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-100">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta http-equiv="Content-Type" content="text/html" />
        <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no, shrink-to-fit=no">
        <title>{{ config('app.name') }} - @yield('title')</title>
        @includeIf('favicons')
        @vite('resources/sass/app.scss')
    </head>
    <body class="h-100">
        <div class="container h-100 d-flex flex-column justify-content-center align-items-center text-center">
            <h1>Ops! 🤔</h1>
            <p class="m-0 lead">@yield('message')</p>
        </div>
    </body>
</html>
