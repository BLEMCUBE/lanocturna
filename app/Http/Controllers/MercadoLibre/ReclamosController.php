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
		$cliente = MLApp::with('usuario')
			->where('app_id', $client_id)
			->firstOrFail();

		// Ordenes
		$ordenes = DB::table('ml_reclamos as mlr')
			->where('mlr.meli_user_id', $client_id)
			->where('mlr.resource', 'order')
			->whereNotNull('mlo.pack_id')
			->join('ml_ordenes as mlo', 'mlo.orden_id', '=', 'mlr.resource_id')
			->selectRaw($this->baseSelect());


		// Envíos
		$envios = DB::table('ml_reclamos as mlr')
			->where('mlr.meli_user_id', $client_id)
			->where('mlr.resource', 'shipment')
			->whereNotNull('mlo.pack_id')
			->join('ml_ordenes as mlo', 'mlo.envio_id', '=', 'mlr.resource_id')
			->selectRaw($this->baseSelect());

		// UNION
		$query = $ordenes->unionAll($envios);

		// Subquery para poder filtrar y paginar
		$reclamos = DB::query()->fromSub($query, 'r');

		// 🔎 Filtros SQL
		if ($request->filled('estado')) {
			$reclamos->where('status', $request->estado);
		}

		if ($request->filled(['inicio', 'fin'])) {
			$reclamos->whereBetween('fecha_orden', [
				$request->inicio . ' 00:00:00',
				$request->fin . ' 23:59:59'
			]);
		}

		if ($request->filled('buscar')) {
			$reclamos->where('pack_id', 'like', '%' . $request->buscar . '%');
		}

		// Ordenar
		$reclamos->orderByDesc('fecha_orden');

		// Paginar en DB ✅
		$datos = $reclamos->paginate(20)->withQueryString();

		return Inertia::render('MercadoLibre/Reclamos', [
			'client_id' => $client_id,
			'datos' => new MLReclamoCollection($datos),
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
	}
	private function baseSelect()
	{
		return "
        mlo.orden_id as orden_id,
        mlo.pack_id as pack_id,
        mlr.reclamo_id as reclamo_id,
        mlr.resource_id as resource_id,
        mlr.status as status,
        mlo.envio_id as envio_id,
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
        ) AS fecha_orden
    ";
	}
}
