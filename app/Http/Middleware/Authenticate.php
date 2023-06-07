<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return null|string
     */
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) return null;

        switch ($request->segment(1)) {
            case 'cliente':
                return route('web.user.signIn');
                break;

            case 'admin':
                return route('web.admin.signIn');
                break;

            default:
                if (array_search('auth:user', $request->route()->middleware()) !== false) return route('web.user.signIn');
                if (array_search('auth:admin', $request->route()->middleware())) return route('web.admin.signIn');
                break;
        }
    }
}
