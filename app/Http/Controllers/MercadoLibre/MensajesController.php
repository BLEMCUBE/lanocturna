<?php

namespace App\Http\Controllers\MercadoLibre;

use App\Http\Controllers\Controller;
use App\Http\Resources\MensajeCollection;
use App\Models\MercadoLibreMensaje;
use App\Models\MercadoLibreVenta;
use Illuminate\Support\Facades\DB;
use App\Services\MensajeService;
use App\Services\MercadoLibreService;
use App\Services\MLUsuarioService;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\Request as Req;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class MensajesController extends Controller
{

	public function index()
	{
		$datos = MercadoLibreMensaje::withVenta()
			->where('is_from_seller', '=', 0)
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
			'datos' => $datosFinal,
		]);
	}

	public function sinLeer()
	{
		$datos = MercadoLibreMensaje::withVenta()
			->orderByDesc('date_created')
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
			'datos' => $datosFinal,
		]);
	}

	public function showMensajes($id)
	{
		$noLeidos=MercadoLibreMensaje::where('is_read',0)->where('pack_id',$id)->count();
		if($noLeidos>0){
			$this->marcarLeido($id);
		}
		$datos = MercadoLibreVenta::where('pack_id', $id)
		->orWhere('mercadolibre_venta_id', $id)
		->select('mercadolibre_venta_id', 'pack_id', 'payload')
		->first();
		$mensaje = app(MensajeService::class);

		$detalle = $mensaje->mensajesDetalleMejorado($id, $datos);


		return Inertia::render('MercadoLibre/MensajesDetalle', [
			'datos' => $detalle,
		]);
	}

	public function descargarAdjunto(Request $request)
	{
		$user = app(MLUsuarioService::class)->datosUsuario();
		if (!$user) return;
		$ml = app(MercadoLibreService::class);


		$request->validate([
			'filename'  => 'required',
			'original_filename' => 'required',
		]);

		$realName = $request->original_filename;
		$filename = $request->filename;

		$token = $ml->getAccessToken($user->meli_user_id);  // renueva automáticamente el access token

		$url = "https://api.mercadolibre.com/messages/attachments/{$filename}?tag=post_sale&site_id=MLU";

		try {
			$response = Http::withToken($token)->withOptions([
				'stream' => true,
			])->get($url);

			if (!$response->successful()) {
				return response()->json([
					'success' => false,
					'message' => 'Error al descargar adjunto',
					'status' => $response->status(),
					'body' => $response->body(),
				], 400);
			}

			// Tipo MIME real
			$contentType = $response->header('Content-Type');

			return response()->streamDownload(function () use ($response) {
				echo $response->body();
			}, $realName, [
				'Content-Type' => $contentType,
			]);
		} catch (\Exception $e) {
			return response()->json([
				'success' => false,
				'message' => 'Error inesperado',
				'error' => $e->getMessage(),
			], 500);
		}
	}

	public function responder(Request $request)
	{
		$request->merge(['date_created' => now()]);
		$user = app(MLUsuarioService::class)->datosUsuario();
		if (!$user) return;
		$ml = app(MercadoLibreService::class);

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
		MercadoLibreMensaje::updateOrCreate(
			['message_id' => $respuestaML['id']],
			[
				'pack_id' => $request->packId,
				'message_id' => $respuestaML['id'],
				'from_user_id' => $respuestaML['from']['user_id'] ?? null,
				'to_user_id'   => $respuestaML['to']['user_id'] ?? null,
				'date_created' => $created,
				'body' => $request->text,
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
	public function marcarLeido($packId)
	{
		$offset = 0;
		$limit = 50;

		$parametros = [
			'tag' => 'post_sale',
			//'mark_as_read' => false,
			'offset' => $offset,
			'limit' => $limit,
		];
		$user = app(MLUsuarioService::class)->datosUsuario();
		if (!$user) return;
		$ml = app(MercadoLibreService::class);
		do {
			$response = $ml->apiGet("/messages/packs/".$packId."/sellers/".$user->meli_user_id , $user->meli_user_id, $parametros);
			$messages = $response['messages'] ?? [];

			foreach ($messages as $msg) {
				//$created = $msg['message_date']['created'] ?? null;
				$read = $msg['message_date']['read'] ?? null;
				// dato clave para saber si el vendedor lo envió
				$fromSeller = isset($msg['from']['user_id'])
					&& strval($msg['from']['user_id']) === strval($user->meli_user_id);
				$pack_id = collect($msg['message_resources'])
					->firstWhere('name', 'packs')['id'] ?? null;
				MercadoLibreMensaje::updateOrCreate(
					['message_id' => $msg['id']],
					[
						'pack_id' => $pack_id,
						'message_id' => $msg['id'],
						'from_user_id' => $msg['from']['user_id'] ?? null,
						'to_user_id'   => $msg['to']['user_id'] ?? null,
					//	'date_created' => $created,
						'body' => strval($msg['text']),
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
}
