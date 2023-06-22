<?php

namespace App\Http\Middleware;

use Inertia\Middleware;
use Tightenco\Ziggy\Ziggy;
use Illuminate\Http\Request;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     * @see https://inertiajs.com/asset-versioning

     * @param \Illuminate\Http\Request $request
     *
     * @return null|string
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Defines the props that are shared by default.
     * @see https://inertiajs.com/shared-data

     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'admin' => $request->user('admin'),
            'user' => $request->user('user'),
            'ziggy' => fn () => array_merge((new Ziggy)->toArray(), ['location' => $request->url()]),
        ]);
    }
}
