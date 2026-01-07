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
		$selects = ['mlo.sku'];

		foreach ($clientIds as $index => $clientId) {
			$index += 1;

			// **CONTEOS (lo que ya tenías)**
			$selects[] = DB::raw(
				"SUM(CASE WHEN mlo.client_id = '{$clientId}' THEN 1 ELSE 0 END) as conteo_tienda_{$index}"
			);

			// **UNIDADES (lo que necesitas)**
			$selects[] = DB::raw(
				"SUM(CASE WHEN mlo.client_id = '{$clientId}'
                THEN COALESCE(mlo.direct_units_quantity, 0) + COALESCE(mlo.indirect_units_quantity, 0)
                ELSE 0
            END) as tienda_{$index}"
			);
		}

		// Suma global de unidades
		$selects[] = DB::raw(
			"SUM(COALESCE(mlo.direct_units_quantity, 0) + COALESCE(mlo.indirect_units_quantity, 0)) as total_units"
		);

		$selects[] = DB::raw("SUM(COALESCE(mlo.direct_units_quantity, 0)) as total_direct");
		$selects[] = DB::raw("SUM(COALESCE(mlo.indirect_units_quantity, 0)) as total_indirect");

		// Columna ambas_tiendas basada en unidades
		if (count($clientIds) == 2) {
			$selects[] = DB::raw(
				"CASE
            WHEN SUM(CASE WHEN mlo.client_id = '{$clientIds[0]}'
                    THEN COALESCE(mlo.direct_units_quantity, 0) + COALESCE(mlo.indirect_units_quantity, 0)
                    ELSE 0
                END) > 0
             AND SUM(CASE WHEN mlo.client_id = '{$clientIds[1]}'
                    THEN COALESCE(mlo.direct_units_quantity, 0) + COALESCE(mlo.indirect_units_quantity, 0)
                    ELSE 0
                END) > 0
            THEN TRUE
            ELSE FALSE
        END as ambas_tiendas"
			);
		}

		// Consulta principal
		$datosFinal = DB::table('ml_campaign_items as mlo')
			->select($selects)
			->where('mlo.status', 'active')
			->whereIn('mlo.client_id', $clientIds)
			->when(FaRequest::input('buscar'), function ($query) {
				$query->where(DB::raw('lower(mlo.sku)'), 'LIKE', '%' . strtolower(FaRequest::input('buscar')) . '%');
			})
			->when($filtro['inicio'], function ($query) use ($filtro) {
				$query->whereDate('mlo.fecha', '>=', $filtro['inicio'] . ' 00:00:00');
			})
			->when(FaRequest::input('fin'), function ($query) {
				$query->whereDate('mlo.fecha', '<=', FaRequest::input('fin') . ' 23:59:00');
			})
			->groupBy('mlo.sku')
			->paginate(500)
			->withQueryString();

		return Inertia::render('MercadoLibre/Publicitados', [
			'datos' => $datosFinal,
			'tiendas' => $tiendasMap,
			'filtro' => $filtro, // Envías siempre las fechas (las del usuario o las generadas)
		]);
	}

	public function indexDos(Request $request)
	{

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
		$selects = ['mlo.sku'];

		foreach ($clientIds as $index => $clientId) {
			$index += 1;

			// **CONTEOS (lo que ya tenías)**
			$selects[] = DB::raw(
				"SUM(CASE WHEN mlo.client_id = '{$clientId}' THEN 1 ELSE 0 END) as conteo_tienda_{$index}"
			);

			// **UNIDADES (lo que necesitas)**
			$selects[] = DB::raw(
				"SUM(CASE WHEN mlo.client_id = '{$clientId}'
                THEN COALESCE(mlo.direct_units_quantity, 0) + COALESCE(mlo.indirect_units_quantity, 0)
                ELSE 0
            END) as tienda_{$index}"
			);
		}

		// Suma global de unidades
		$selects[] = DB::raw(
			"SUM(COALESCE(mlo.direct_units_quantity, 0) + COALESCE(mlo.indirect_units_quantity, 0)) as total_units"
		);

		$selects[] = DB::raw("SUM(COALESCE(mlo.direct_units_quantity, 0)) as total_direct");
		$selects[] = DB::raw("SUM(COALESCE(mlo.indirect_units_quantity, 0)) as total_indirect");

		// Columna ambas_tiendas basada en unidades
		if (count($clientIds) == 2) {
			$selects[] = DB::raw(
				"CASE
            WHEN SUM(CASE WHEN mlo.client_id = '{$clientIds[0]}'
                    THEN COALESCE(mlo.direct_units_quantity, 0) + COALESCE(mlo.indirect_units_quantity, 0)
                    ELSE 0
                END) > 0
             AND SUM(CASE WHEN mlo.client_id = '{$clientIds[1]}'
                    THEN COALESCE(mlo.direct_units_quantity, 0) + COALESCE(mlo.indirect_units_quantity, 0)
                    ELSE 0
                END) > 0
            THEN TRUE
            ELSE FALSE
        END as ambas_tiendas"
			);
		}

		// Consulta principal
		$datosFinal = DB::table('ml_campaign_items as mlo')
			->select($selects)
			->where('mlo.status', 'active')
			->whereIn('mlo.client_id', $clientIds)
			->when(FaRequest::input('buscar'), function ($query) {
				$query->where(DB::raw('lower(mlo.sku)'), 'LIKE', '%' . strtolower(FaRequest::input('buscar')) . '%');
			})
			->when($filtro['inicio'], function ($query) use ($filtro) {
				$query->whereDate('mlo.fecha', '>=', $filtro['inicio'] . ' 00:00:00');
			})
			->when(FaRequest::input('fin'), function ($query) {
				$query->whereDate('mlo.fecha', '<=', FaRequest::input('fin') . ' 23:59:00');
			})
			->groupBy('mlo.sku')
			->paginate(100)
			->withQueryString();

		return Inertia::render('MercadoLibre/Publicitados', [
			'datos' => $datosFinal,
			'tiendas' => $tiendasMap,
			'filtro' => $filtro, // Envías siempre las fechas (las del usuario o las generadas)
		]);
	}
}
