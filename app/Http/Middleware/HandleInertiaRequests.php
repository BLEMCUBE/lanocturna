<?php

namespace App\Http\Middleware;

use App\Models\TipoCambio;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tightenco\Ziggy\Ziggy;
use App\Http\Resources\VentaCollection;
use App\Models\Venta;

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

        // cantidad de rmas
        $total_rmas = new VentaCollection(
            Venta::where(function ($query) {
                $query->where('destino', "CADETERIA")
                    ->orWhere('destino', "FLEX")
                    ->orWhere('destino', "UES")
                    ->orWhere('destino', "DAC")
                    ->orWhere('destino', "WEB")
                    ->orWhere('destino', "MERCADOLIBRE")
                    ->orWhere('destino', "SALON");
            })->select('*')->when($request->input('inicio'), function ($query, $search) {
                $query->whereDate('created_at', '>=', $search);
            })
                ->when($request->input('fin'), function ($query, $search) {
                    $query->whereDate('created_at', '<=', $search);
                })
                ->where("tipo", '=', "RMA")
                ->where("facturado", '=', "0")
                ->orderBy('created_at', 'DESC')->get()
        );
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user(),
                'roles' => $request->user() ? $request->user()->roles->pluck('name') : [],
                'permissions' => $request->user() ? $request->user()->getPermissionsViaRoles()->pluck('name') : [],
                'hoy_tipo_cambio' => $hoy_tipo_cambio,
                'total_rmas'=>$total_rmas->count()
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
