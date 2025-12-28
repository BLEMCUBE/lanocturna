<?php

namespace App\Http\Controllers\MercadoLibre;

use App\Http\Controllers\Controller;
use App\Http\Resources\MLVentaCollection;
use App\Models\MLApp;
use App\Models\MLOrden;
use App\Helpers\MercadoLibreShippingHelper;
use App\Http\Resources\MLVentaPackResource;
use App\Http\Resources\MLVentaSimpleResource;
use App\Services\MercadoLibre\MLAppService;
use App\Services\MercadoLibre\OrdenService;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class VentasController extends Controller
{

	public function index($client_id, Request $request)
	{
		// Obtener cliente y usuario
		$cliente = MLApp::with('usuario')
			->where('app_id', $client_id)
			->first();


		if (!$cliente || !$cliente->usuario) {
			abort(404, 'Cliente no encontrado');
		}

		$seller_id = $cliente->usuario->meli_user_id;

		// Subconsultas de órdenes y packs
		$query1 = DB::table('ml_ordenes')
			->where('seller_id', $seller_id)
			->whereNotNull('pack_id')
			->selectRaw("pack_id AS venta_id, 'pack' AS tipo, created_at, status,seller_id,
			DATE_FORMAT(
  STR_TO_DATE(
    SUBSTRING_INDEX(
      JSON_UNQUOTE(JSON_EXTRACT(payload, '$.date_created')),
      '.',
      1
    ),
    '%Y-%m-%dT%H:%i:%s'
  ),
   '%Y-%m-%d %H:%i:%s'
) AS pp");

		$query2 = DB::table('ml_ordenes')
			->where('seller_id', $seller_id)
			->whereNull('pack_id')
			->selectRaw("orden_id AS venta_id, 'orden' AS tipo, created_at, status,seller_id,
			DATE_FORMAT(
  STR_TO_DATE(
    SUBSTRING_INDEX(
      JSON_UNQUOTE(JSON_EXTRACT(payload, '$.date_created')),
      '.',
      1
    ),
    '%Y-%m-%dT%H:%i:%s'
  ),
  '%Y-%m-%d %H:%i:%s'
) AS pp");

		/** @var \Illuminate\Support\Collection<int, object> $rows */
		// Ejecutar union y traer resultados
		$rows = collect($query1->union($query2)->get());

		// Filtrar búsqueda
		if ($request->filled('buscar')) {
			$buscar = $request->buscar;
			$rows = $rows->filter(function ($r) use ($buscar) {
				return str_contains($r->venta_id, $buscar);
			});
		}

		// Filtrar estado
		if ($request->filled('estado')) {
			$estado = $request->estado;
			$rows = $rows->filter(fn($r) => $r->status == $estado);
		}

		// Filtrar rango de fechas
		if ($request->filled(['inicio', 'fin'])) {
			$inicio = $request->inicio . ' 00:00:00';
			$fin = $request->fin . ' 23:59:59';
			$rows = $rows->filter(fn($r) => $r->pp >= $inicio && $r->pp <= $fin);
		}

		// Agrupar por venta_id (tomando la primera ocurrencia)
		$rows = $rows->groupBy('venta_id')->map(fn($group) => $group->first());

		// Ordenar por fecha descendente
		$rows = $rows->sortByDesc('pp')->values();

		// Paginar manualmente
		$perPage = 20;
		$page = $request->input('page', 1);
		$total = $rows->count();
		$paginated = new LengthAwarePaginator(
			$rows->forPage($page, $perPage),
			$total,
			$perPage,
			$page,
			['path' => $request->url(), 'query' => $request->query()]
		);

		// Enviar a colección de recursos (si usas MLVentaCollection)
		$datosFinal = new MLVentaCollection($paginated);

		return Inertia::render('MercadoLibre/Ventas', [
			'client_id' => $client_id,
			'datos' => $datosFinal,
			'tienda' => app(MLAppService::class)->getNombre($client_id),
			'filtro' => $request->only(['buscar', 'inicio', 'fin', 'estado']),
		]);
	}


	public function show($client_id, $venta_id, $tipo)
	{

		if ($tipo == 'orden') {

			$envio = MLOrden::where('orden_id', $venta_id)->firstOrFail();

			//act datos

			$this->actDatos($client_id, $venta_id, $envio);
			// RECARGAR REGISTRO YA ACTUALIZADO
			$dato = MLOrden::where('orden_id', $venta_id)->first();


			// CREAR RESOURCE
			$venta = new MLVentaSimpleResource($dato);

			// PASAR RESOURCE A INERTIA
			return Inertia::render('MercadoLibre/VentasDetalle', [
				'client_id' => $client_id,
				'tienda' => app(MLAppService::class)->getNombre($client_id),
				'datos' => $venta
			]);
		} else {
			$datos = MLOrden::where('pack_id', $venta_id)->get();
			foreach ($datos as $key => $da) {
				$envio = MLOrden::where('orden_id', $da->orden_id)->first();
				//act datos

				$this->actDatos($client_id, $da->orden_id, $envio);
			}
			$dato = MLOrden::where('pack_id', $venta_id)->get();

			// CREAR RESOURCE
			$venta = new MLVentaPackResource($dato);

			// PASAR RESOURCE A INERTIA

			return Inertia::render('MercadoLibre/VentasDetalle', [
				'client_id' => $client_id,
				'tienda' => app(MLAppService::class)->getNombre($client_id),
				'datos' => $venta
			]);
		}
	}

	public function actDatos($client_id, $venta_id, $envio)
	{
		// =====================================================================
		// GUARDAR ENVÍO SI NO EXISTE
		// =====================================================================
		if (is_null($envio->envio)) {
			app(OrdenService::class)->agregarEnvio($venta_id, $client_id);
		}

		// =====================================================================
		// GUARDAR FACTURACIÓN SI NO EXISTE
		// =====================================================================
		if (is_null($envio->facturacion)) {
			app(OrdenService::class)->agregarFacturacion($venta_id, $client_id);
		}
		if (is_null($envio->costo_envio)) {
			app(OrdenService::class)->agregarCostoEnvio($venta_id, $client_id);
		}
		if (!is_null($envio->envio)) {
			$result = MercadoLibreShippingHelper::whoPays($envio->payload, $envio->envio);

			$envio->update([
				'shipping_paid_by'        => $result['who_pays'],
				'shipping_buyer_cost'     => $result['buyer_cost'],
				'shipping_seller_cost'    => $result['seller_cost'],
				'shipping_detected_by'    => $result['detected_by'],
			]);
		}
	}
}
