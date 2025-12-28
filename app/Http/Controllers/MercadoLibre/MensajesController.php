<?php

namespace App\Http\Controllers\MercadoLibre;

use App\Http\Controllers\Controller;
use App\Http\Resources\MensajeCollection;
use App\Models\MLApp;
use App\Models\MLMensaje;
use App\Models\MLClient;
use App\Models\MLOrden;
use App\Services\MercadoLibre\MensajeService;
use App\Services\MercadoLibre\MercadoLibreService;
use App\Services\MercadoLibre\MLAppService;
use Inertia\Inertia;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class MensajesController extends Controller
{

	public function index($client_id)
	{
		$datos = MLMensaje::withVenta()
			->where('is_from_seller', '=', 0)
			->where('client_id', '=', $client_id)
			->orderByDesc('date_created')
			->get()
			->unique('pack_id')
			->values()
			->map(function ($mensaje) {
				$mensaje->venta = $mensaje->ventaPorPack ?? $mensaje->ventaPorId;
				return $mensaje;
			});
		$datosFinal = new MensajeCollection($datos);
		return Inertia::render('MercadoLibre/Mensajes', [
			'client_id' => $client_id,
			'tienda' => app(MLAppService::class)->getNombre($client_id),
			'datos' => $datosFinal,
		]);
	}

	public function sinLeer($client_id)
	{
		$datos = MLMensaje::withVenta()
			->orderByDesc('date_created')
			->where('client_id', '=', $client_id)
			->where('is_from_seller', '=', 0)
			->where('is_read', '=', 0)
			->get()
			->unique('pack_id')
			->values()
			->map(function ($mensaje) {
				$mensaje->venta = $mensaje->ventaPorPack ?? $mensaje->ventaPorId;
				return $mensaje;
			});
		$datosFinal = new MensajeCollection($datos);
		return Inertia::render('MercadoLibre/MensajesSinLeer', [
			'client_id' => $client_id,
			'tienda' => app(MLAppService::class)->getNombre($client_id),
			'datos' => $datosFinal,
		]);
	}

	public function showMensajes($client_id, $id)
	{
		$noLeidos = MLMensaje::where('is_read', 0)
			->where('client_id', '=', $client_id)->where('pack_id', $id)->count();
		if ($noLeidos > 0) {
			$this->marcarLeido($id, $client_id);
		}
		$datos = MLOrden::where('pack_id', $id)
			->orWhere('orden_id', $id)
			->select('orden_id', 'pack_id', 'payload')
			->first();
		$mensaje = app(MensajeService::class);

		$detalle = $mensaje->mensajesDetalleMejorado($id, $datos);


		return Inertia::render('MercadoLibre/MensajesDetalle', [
			'client_id' => $client_id,
			'tienda' => app(MLAppService::class)->getNombre($client_id),
			'datos' => $detalle,
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

		$url = "https://api.mercadolibre.com/messages/attachments/{$filename}?tag=post_sale&site_id=MLU";

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

		$user = MLClient::with('cliente')
			->where('meli_user_id', $request->sellerId)
			->first();
		if (!$user) return;
		$ml = app(MercadoLibreService::class)->forClient($user->cliente->app_id);

		//enviar a mercado libre
		$respuestaML = $ml->apiPost("/messages/packs/{$request->packId}/sellers/{$request->sellerId}?tag=post_sale",  [
			"from" => [
				"user_id" => (int) $request->sellerId
			],
			"to" => [
				"user_id" => (int) $request->buyerId //null // ML lo detecta automáticamente por ser mensaje al comprador
			],
			"text" => $request->text
		], $user->meli_user_id);

		$respuestaML;
		$created = $respuestaML['message_date']['created'] ?? null;
		MLMensaje::updateOrCreate(
			['message_id' => $respuestaML['id']],
			[
				'client_id' => $request->clientId,
				'pack_id' => $request->packId,
				'message_id' => $respuestaML['id'],
				'from_user_id' => $respuestaML['from']['user_id'] ?? null,
				'to_user_id'   => $respuestaML['to']['user_id'] ?? null,
				'date_created' => $created,
				'text' => $request->text,
				'attachment_path' => $respuestaML['message_attachments'][0]['filename']
					?? null,
				// si read ≠ null → lo leyó alguien → marcar como leído
				'is_read' =>  0,
				// marcar si lo envió el vendedor
				'is_from_seller' => 1,
				// guardar JSON entero
				'payload' => $respuestaML,


			]
		);
	}
	public function marcarLeido($packId, $client_id)
	{
		$offset = 0;
		$limit = 50;

		$parametros = [
			'tag' => 'post_sale',
			//'mark_as_read' => false,
			'offset' => $offset,
			'limit' => $limit,
		];

		$cliente = MLApp::with('usuario')
			->where('app_id', $client_id)->first();
		if (!$cliente) return;
		$ml = app(MercadoLibreService::class)->forClient($client_id);
		do {
			$response = $ml->apiGet("/messages/packs/" . $packId . "/sellers/" . $cliente->usuario->meli_user_id, $cliente->usuario->meli_user_id, $parametros);
			$messages = $response['messages'] ?? [];

			foreach ($messages as $msg) {
				//$created = $msg['message_date']['created'] ?? null;
				$read = $msg['message_date']['read'] ?? null;
				// dato clave para saber si el vendedor lo envió
				$fromSeller = isset($msg['from']['user_id'])
					&& strval($msg['from']['user_id']) === strval($cliente->usuario->meli_user_id);
				$pack_id = collect($msg['message_resources'])
					->firstWhere('name', 'packs')['id'] ?? null;
				MLMensaje::updateOrCreate(
					['message_id' => $msg['id']],
					[
						'pack_id' => $pack_id,
						'message_id' => $msg['id'],
						'from_user_id' => $msg['from']['user_id'] ?? null,
						'to_user_id'   => $msg['to']['user_id'] ?? null,
						//	'date_created' => $created,
						'text' => strval($msg['text']),
						'attachment_path' => $msg['message_attachments'][0]['filename']
							?? null,
						// si read ≠ null → lo leyó alguien → marcar como leído
						'is_read' => $read ? 1 : 0,
						// marcar si lo envió el vendedor
						'is_from_seller' => $fromSeller ? 1 : 0,
						// guardar JSON entero
						'payload' => $msg,
					]
				);
			}

			// Paginación
			$offset += $limit;
			$total = $response['paging']['total'] ?? $offset;
		} while ($offset < $total);
	}

	public function setAppId($client_id)
	{

		$items = MLMensaje::get();
		foreach ($items as $value) {
			$value->update(['client_id' => $client_id]);
		}
		return response()->json([
			"item" => "ok"
		]);
	}
}
