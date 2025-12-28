<?php

namespace App\Http\Controllers\MercadoLibre;

use App\Http\Controllers\Controller;
use App\Models\MLApp;
use App\Http\Resources\MLReclamoCollection;
use App\Services\MercadoLibre\MercadoLibreService;
use App\Services\MercadoLibre\MLAppService;
use App\Services\MercadoLibre\ReclamoService;
use Inertia\Inertia;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ReclamosController extends Controller
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


		// Subconsultas ordenes
		$query1 = DB::table('ml_reclamos as mlr')
			->where('mlr.meli_user_id', $client_id)
			->where('mlr.resource', 'order')
			->join('ml_ordenes as mlo', 'mlo.orden_id', '=', 'mlr.resource_id')

			->selectRaw("mlo.orden_id as orden_id,mlr.reclamo_id as reclamo_id,mlr.resource_id as resource_id,mlr.status as status,mlo.envio_id,
			DATE_FORMAT(
  STR_TO_DATE(
    SUBSTRING_INDEX(
      JSON_UNQUOTE(JSON_EXTRACT(mlr.payload, '$.date_created')),
      '.',
      1
    ),
    '%Y-%m-%dT%H:%i:%s'
  ),
   '%Y-%m-%d %H:%i:%s'
) AS fecha_orden");


		//envios
		$query2 = DB::table('ml_reclamos as mlr')
			->where('mlr.meli_user_id', $client_id)
			->where('mlr.resource', 'shipment')
			->join('ml_ordenes as mlo', 'mlo.envio_id', '=', 'mlr.resource_id')

			->selectRaw("mlo.orden_id as orden_id,mlr.reclamo_id as reclamo_id,mlr.resource_id as resource_id,mlr.status as status,mlo.envio_id,
			DATE_FORMAT(
  STR_TO_DATE(
    SUBSTRING_INDEX(
      JSON_UNQUOTE(JSON_EXTRACT(mlr.payload, '$.date_created')),
      '.',
      1
    ),
    '%Y-%m-%dT%H:%i:%s'
  ),
   '%Y-%m-%d %H:%i:%s'
) AS fecha_orden");



		/** @var \Illuminate\Support\Collection<int, object> $rows */
		// Ejecutar union y traer resultados
		$rows = collect($query1->union($query2)->get());

		// Filtrar estado
		if ($request->filled('estado')) {
			$estado = $request->estado;
			$rows = $rows->filter(fn($r) => $r->status === $estado);
		}

		// Filtrar rango de fechas
		if ($request->filled(['inicio', 'fin'])) {
			$inicio = $request->inicio . ' 00:00:00';
			$fin = $request->fin . ' 23:59:59';
			$rows = $rows->filter(fn($r) => $r->fecha_orden >= $inicio && $r->fecha_orden <= $fin);
		}

		// Ordenar por fecha descendente
		$rows =
			$rows
			->sortByDesc(function ($row) {
				return strtotime($row->fecha_orden);
			})
			/*->sortByDesc(function ($row) {
				return $row->status === 'opened';
			})*/
			->values();

		// Filtrar búsqueda
		if ($request->filled('buscar')) {
			$buscar = strtolower($request->buscar);

			$rows = $rows->filter(function ($r) use ($buscar) {
				// Convertimos a string y lowercase para evitar errores y hacer búsqueda insensible a mayúsculas
				return str_contains(strtolower((string) $r->resource_id), $buscar);
			})->values(); // Reindexa la collection
		}

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
		$datosFinal = new MLReclamoCollection($paginated);

		return Inertia::render('MercadoLibre/Reclamos', [
			'client_id' => $client_id,
			'datos' => $datosFinal,
			'tienda' => app(MLAppService::class)->getNombre($client_id),
			'filtro' => $request->only(['buscar', 'inicio', 'fin', 'estado']),
		]);
	}


	public function show($client_id, $reclamo_id)
	{
		$reclamo = app(ReclamoService::class);
		$detalle = $reclamo->mensajesDetalleMejorado($reclamo_id);
		return Inertia::render('MercadoLibre/ReclamosDetalle', [
			'client_id' => $client_id,
			'tienda' => app(MLAppService::class)->getNombre($client_id),
			'datos' => $detalle
		]);
	}

	public function descargarAdjunto(Request $request)
	{
		$request->validate([
			'filename'  => 'required',
			'original_filename' => 'required',
			'client_id' => 'required'
		]);

		$realName = $request->original_filename;
		$filename = $request->filename;

		$cliente = MLApp::with('usuario')
			->where('app_id', $request->client_id)
			->first();

		if (!$cliente) {
			abort(404, 'Cliente no encontrado');
		}

		$ml = app(MercadoLibreService::class)->forClient($request->client_id);
		$token = $ml->getAccessToken($cliente->usuario->meli_user_id);

		$url = "https://api.mercadolibre.com/post-purchase/v1/claims/{$request->reclamo_id}/attachments/{$filename}/download";

		try {
			// LA CLAVE: headers correctos + stream
			$response = Http::withToken($token)
				->withHeaders([
					'Accept' => '*/*',
					'X-Meli-Format' => 'binary' // <-- IMPORTANTE PARA PDFs, JPG, ZIP
				])
				->withOptions(['stream' => true])
				->get($url);

			if (!$response->successful()) {
				return abort(400, "Error al descargar adjunto: API ML");
			}

			$stream = $response->toPsrResponse()->getBody();
			$contentType = $response->header('Content-Type') ?? 'application/octet-stream';

			return response()->streamDownload(function () use ($stream) {
				while (!$stream->eof()) {
					echo $stream->read(1024 * 8); // leer en chunks de 8KB
				}
			}, $realName, [
				'Content-Type' => $contentType
			]);
		} catch (\Throwable $e) {
			return abort(500, "Error interno: " . $e->getMessage());
		}
	}

	public function responder(Request $request)
	{
		$request->merge(['date_created' => now()]);

		$cliente = MLApp::with('usuario')
			->where('app_id', $request->clientId)
			->first();
		if (!$cliente) return;

		$request->validate([
			'text' => 'required|string',
			'files' => 'nullable|array',
			'files.*' => 'file|max:5120'
		]);


		$attachments = [];

		if ($request->hasFile('files')) {
			$uploaded = app(ReclamoService::class)->uploadAttachments(
				$request->clientId,
				$request->reclamoId,
				$request->file('files')
			);

			$attachments = collect($uploaded)->pluck('id')->toArray();
		}

		$response = app(ReclamoService::class)->sendMessageWithAttachments(
			$request->clientId,
			$request->reclamoId,
			$request->text,
			$attachments
		);

		/*return response()->json([
			'success' => true,
			'message' => 'Mensaje enviado correctamente',
			'ml_response' => $response
		]);*/
	}
}
