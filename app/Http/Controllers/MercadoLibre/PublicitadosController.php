<?php

namespace App\Http\Controllers\MercadoLibre;

use App\Http\Controllers\Controller;
use App\Models\MLApp;
use App\Models\MLOrden;
use Inertia\Inertia;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as FaRequest;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PublicitadosController extends Controller
{

	public function index(Request $request)
	{

		$clientes = MLApp::with('usuario')
			->whereHas('usuario')
			->get();
		$tiendasMap = $clientes->map(function ($t) {
			return [
				'app_id' => $t['app_id'],
				'nombre' => str()->slug($t['nombre'], '_')
			];
		});

		$clientIds = $clientes->pluck('app_id')->filter()->values()->toArray();

		// Creamos selects dinámicos
		$selects = ['mlo.item_sku'];

		foreach ($clientIds as $index => $clientId) {
			$index += 1; // Para empezar en 1
			$selects[] = DB::raw(
				"SUM(CASE WHEN mlo.client_id = '{$clientId}' THEN 1 ELSE 0 END) as tienda_{$index}"
			);
		}

		// Columna extra: true si tiene ventas en ambas tiendas
		if (count($clientIds) == 2) {
			$selects[] = DB::raw(
				"CASE
            WHEN SUM(CASE WHEN mlo.client_id = '{$clientIds[0]}' THEN 1 ELSE 0 END) > 0
             AND SUM(CASE WHEN mlo.client_id = '{$clientIds[1]}' THEN 1 ELSE 0 END) > 0
            THEN TRUE
            ELSE FALSE
        END as ambas_tiendas"
			);
		}

		// Consulta principal
		$datosFinal = DB::table('ml_ordenes as mlo')
			->join('ml_items as mli', 'mli.item_id', '=', 'mlo.item_id')
			->select($selects)
			->where('mli.status', 'active')
			//->where('mlo.status', 'paid')
			->whereIn('mlo.client_id', $clientIds)
			->when(FaRequest::input('buscar'), function ($query) {
				$query->where(DB::raw('lower(mlo.item_sku)'), 'LIKE', '%' . strtolower(FaRequest::input('buscar')) . '%');
			})
			->when(FaRequest::input('inicio'), function ($query) {
				$query->whereDate('mlo.date_created', '>=', FaRequest::input('inicio') . ' 00:00:00');
			})
			->when(FaRequest::input('fin'), function ($query) {
				$query->whereDate('mlo.date_created', '<=', FaRequest::input('fin') . ' 23:59:00');
			})
			->groupBy('mlo.item_sku')
			->paginate(100)
			->withQueryString();

		return Inertia::render('MercadoLibre/Publicitados', [
			'datos' => $datosFinal,
			'tiendas' => $tiendasMap,
			'filtro' =>  $request->only(['inicio', 'fin', 'buscar']),
		]);
	}
}
