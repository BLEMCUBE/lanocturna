<?php

namespace App\Http\Middleware;

use App\Models\TipoCambio;
use Carbon\Carbon;
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
        $ultimo_tipo_cambio=TipoCambio::all()->last();

        $hoy_tipo_cambio=false;

        $actual=Carbon::now()->format('Y-m-d');
        if(!empty($ultimo_tipo_cambio)){
            $fecha=Carbon::create($ultimo_tipo_cambio->created_at->format('Y-m-d'));
        if($fecha->eq($actual)){
            $hoy_tipo_cambio=true;
            $tipo_cambio=$ultimo_tipo_cambio;
            }else{
                $hoy_tipo_cambio=false;
            }
        }
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user(),
                'roles' => $request->user() ? $request->user()->roles->pluck('name') : [],
                'permissions' => $request->user() ? $request->user()->getPermissionsViaRoles()->pluck('name') : [],
                'hoy_tipo_cambio' => $hoy_tipo_cambio,
                //'notificaciones' => !empty(auth()->user()->unreadNotifications) ?auth()->user()->unreadNotifications: [],
            ],
            //'csrf_token' => csrf_token(),
            'ziggy' => function () use ($request) {
                return array_merge((new Ziggy)->toArray(), [
                    'location' => $request->url(),
                    'query'=>$request->query()
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
