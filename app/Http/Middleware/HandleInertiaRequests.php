<?php

namespace App\Http\Middleware;

use App\Models\TipoCambio;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tightenco\Ziggy\Ziggy;
use App\Http\Resources\VentaCollection;
use App\Models\Compra;
use App\Models\Configuracion;
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
		$ultimo_tipo_cambio = TipoCambio::all()->last();

		$hoy_tipo_cambio = false;

		$actual = Carbon::now()->format('Y-m-d');
		if (!empty($ultimo_tipo_cambio)) {
			$fecha = Carbon::create($ultimo_tipo_cambio->created_at->format('Y-m-d'));
			if ($fecha->eq($actual)) {
				$hoy_tipo_cambio = true;
				//$tipo_cambio = $ultimo_tipo_cambio;
			} else {
				$hoy_tipo_cambio = false;
			}
		} else {
			$hoy_tipo_cambio = false;
		}
		$pagos_compra = Compra::where('pagado', '=', 0)->whereNull('fecha_anulacion')->count();
		// cantidad de rmas
		$total_rmas = new VentaCollection(
			Venta::where(function ($query) {
				$query->where('destino', "CADETERIA")
					->orWhere('destino', "FLEX")
					->orWhere('destino', "UES")
					->orWhere('destino', "UES WEB")
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

		//configuracion
		$configuracion = Configuracion::get();
		//total envios
		$envios =  Venta::where(function ($query) {
			$query->where('destino', "CADETERIA")
				->orWhere('destino', "FLEX")
				->orWhere('destino', "UES")
				->orWhere('destino', "RETIROS WEB")
				->orWhere('destino', "SALON")
				->orWhere('destino', "ENVIO FLASH")
				->orWhere('destino', "UES WEB");
		})->where(function ($query) {
			$query->where('estado', "PENDIENTE DE FACTURACIÓN")
				->orWhere('estado', "PENDIENTE DE VALIDACIÓN")
				->orWhere('estado', "VALIDADO")
				->orWhere('estado', "FACTURADO");
		})
			->select('id', 'destino', 'created_at')
			->orderBy('created_at', 'DESC')->get();

		$total_ues = 0;
		$total_flex = 0;
		$total_dac = 0;
		$total_cadeteria = 0;
		$total_flash = 0;
		$total_expedicion = 0;
		$total_retiro = 0;

		foreach ($envios as $key => $envio) {

			switch ($envio->destino) {
				case 'UES':
					$total_ues += 1;
					break;
				case 'FLEX':
					$total_flex += 1;
					break;
				case 'UES WEB':
					$total_dac += 1;
					break;
				case 'CADETERIA':
					$total_cadeteria += 1;
					break;
				case 'ENVIO FLASH':
					$total_flash += 1;
					break;
				case 'RETIROS WEB':
					$total_retiro += 1;
					break;
				case 'SALON':
					$total_expedicion += 1;
					break;

				default:
					# code...
					break;
			}
		}


		$dato = [
			...parent::share($request),
			'auth' => [
				'user' => $request->user(),
				'roles' => $request->user() ? $request->user()->roles->pluck('name') : [],
				'permissions' => $request->user() ? $request->user()->getPermissionsViaRoles()->pluck('name') : [],
				'hoy_tipo_cambio' => $hoy_tipo_cambio,
				'total_rmas' => $total_rmas->count(),
				'total_ues' => $total_ues,
				'total_flex' => $total_flex,
				'total_dac' => $total_dac,
				'total_cadeteria' => $total_cadeteria,
				'total_flash' => $total_flash,
				'total_expedicion' => $total_expedicion,
				'pagos_compras' => $pagos_compra,
				'total_retiro' => $total_retiro,
				'configuracion' =>
				//'nombre'=>$configuracion->nombre_app,
				$configuracion,
				//'notificaciones' => !empty(auth()->user()->unreadNotifications) ?auth()->user()->unreadNotifications: [],
			],
			//'csrf_token' => csrf_token(),
			'ziggy' => function () use ($request) {
				return array_merge((new Ziggy)->toArray(), [
					'location' => $request->url(),
					'query' => $request->query()
				]);
			},
			'flash' => [
				'error' => session('error'),
				'success' => session('success')
			],

			'base_url' => url('/') . '/'
			//]);
		];
		return $dato;
	}
}
