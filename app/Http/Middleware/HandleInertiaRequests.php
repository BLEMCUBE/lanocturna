<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Tightenco\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request)
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user(),
                //'roles' => $request->user() ? $request->user()->roles->pluck('name') : [],
                //'permissions' => $request->user() ? $request->user()->getPermissionsViaRoles()->pluck('name') : [],
                //'notificaciones' => !empty(auth()->user()->unreadNotifications) ?auth()->user()->unreadNotifications: [],
            ],
            //'csrf_token' => csrf_token(),
            'ziggy' => function () use ($request) {
                return array_merge((new Ziggy)->toArray(), [
                    'location' => $request->url(),
                ]);
            },
            'flash' => [
                'error' => session('error'),
                'success' => session('success')
            ],
            'configuracion'=> [
                //'nombre'=>$configuracion->nombre_app,
              ],
                  'base_url'=>url('/').'/'
        ]);
    }
}
