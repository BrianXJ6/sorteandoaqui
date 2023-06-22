<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param string ...$guards
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;
        $segment = $request->segment(1);

        foreach ($guards as $guard) {
            if ($segment === 'cliente' && $guard === 'user' && auth()->guard($guard)->check())
                return redirect()->intended(route('web.user.dashboard.home'));

            if ($segment === 'admin' && $guard === 'admin' && auth()->guard($guard)->check())
                return redirect()->intended(route('web.admin.dashboard.home'));
        }

        return $next($request);
    }
}
