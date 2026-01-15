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

class VendidosController extends Controller
{

	public function index(Request $request)
	{

		$optSku = MLOrden::whereNotNull('item_sku')
			->orderBy('item_sku')
			->pluck('item_id', 'item_sku');

		// Generar fechas por defecto si no vienen
		$inicio = $request->input('inicio') ?? now()->subDay(7)->format('Y-m-d');
		$fin = $request->input('fin') ?? now()->format('Y-m-d');

		// Validar que fin sea mayor o igual a inicio
		if ($request->has('inicio') && $request->has('fin')) {
			if ($fin < $inicio) {
				// Si fin es menor, ajustar al mismo día
				$fin = $inicio;
			}
		}
		// Crear array con las fechas (incluyendo las generadas)
		$filtro = [
			'inicio' => $inicio,
			'fin' => $fin,
			'buscar' => $request->input('buscar', '') // valor por defecto vacío
		];

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
			$index += 1;

			// **CONTEOS (lo que ya tenías)**
			$selects[] = DB::raw(
				"SUM(CASE WHEN mlo.client_id = '{$clientId}' THEN 1 ELSE 0 END) as tienda_{$index}"
			);


		}


		// Consulta principal
		$datosFinal = DB::table('ml_ordenes as mlo')
			->select($selects)
			->where('mlo.status', 'paid')
			->whereIn('mlo.client_id', $clientIds)
			->when(FaRequest::input('buscar'), function ($query) {
				$query->where(DB::raw('lower(mlo.item_sku)'), 'LIKE', '%' . strtolower(FaRequest::input('buscar')) . '%');
			})
			->when($filtro['inicio'], function ($query) use ($filtro) {
				$query->whereDate('mlo.date_created', '>=', $filtro['inicio'] . ' 00:00:00');
			})
			->when(FaRequest::input('fin'), function ($query) {
				$query->whereDate('mlo.date_created', '<=', FaRequest::input('fin') . ' 23:59:00');
			})
			->groupBy('mlo.item_sku')
			->paginate(500)
			->withQueryString();

		return Inertia::render('MercadoLibre/Vendidos', [
			'datos' => $datosFinal,
			'tiendas' => $tiendasMap,
			'filtro' => $filtro, // Envías siempre las fechas (las del usuario o las generadas)
		]);
	}
}
