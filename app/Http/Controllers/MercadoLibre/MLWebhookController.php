<?php

namespace App\Http\Controllers\MercadoLibre;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MLCLient;
use App\Jobs\ProcessMercadoLibreNotification;
use App\Models\MLApp;
use App\Models\MLNotificacion;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MLWebhookController extends Controller
{
	public function callback(Request $request)
	{
		$code = $request->get('code');
		$cliente_id = $request->get('state');
		$client = MLApp::findOrFail($cliente_id);
		// Intercambiar code por access_token
		$tokenResponse = Http::asForm()->post('https://api.mercadolibre.com/oauth/token', [
			'grant_type' => 'authorization_code',
			'client_id' => $client->app_id,
			'client_secret' => $client->client_secret,
			'code' => $code,
			//'scope'=>"read%20write%20offline_access%20orders%20items%20messages%20shipments",
			'redirect_uri' => route('mercadolibre.callback'),
		]);

		$tokenData = $tokenResponse->json();

		if (isset($tokenData['error'])) {
			return redirect()->route('mercadolibre.apps.index')->with('error', 'Error al obtener el token');
		}

		$accessToken = $tokenData['access_token'];
		// Obtener datos del usuario de Mercado Libre
		$userResponse = Http::withToken($accessToken)->get('https://api.mercadolibre.com/users/me');
		$meliUser = $userResponse->json();
		// Guardar o actualizar el usuario 1 a 1
		$usuario = MLCLient::updateOrCreate(
			['app_id' => $client->id], // garantizamos 1 a 1
			[
				'meli_user_id' => $meliUser['id'],
				'nickname' => $meliUser['nickname'],
				'email' => $meliUser['email'] ?? null,
			]
		);
		// Actualizar tokens del usuario
		$usuario->update([
			'access_token' => $accessToken,
			'refresh_token' => $tokenData['refresh_token'] ?? null,
			'expires_at' => now()->addSeconds($tokenData['expires_in'] ?? 21600),
			'payload' => $tokenData,
		]);
		return redirect()->route('mercadolibre.apps.index')->with('success', 'Cuenta de Mercado Libre vinculada correctamente');
	}


	public function notifications(Request $request)
	{
		$payload = $request->all();
		$resource = $payload['resource'] ?? null;
		$topic = $payload['topic'] ?? null;
		$userId   = $request->user_id;



		if (!$resource) {
			return response()->json(['error' => 'Notificación sin resource'], 400);
		}

		// Normalizar actions a un array
		$actions = $payload['actions'] ?? null;
		if (!is_array($actions)) {
			$actions = $actions ? [$actions] : [];
		}

		// Evitar duplicados (resource + acción)
		$exists = MLNotificacion::where('resource', $resource)
			//->whereIn('status', ['processed', 'received'])
			->where('status', 'received')
			->when(count($actions) > 0, function ($q) use ($actions) {
				$q->whereIn('actions', [
					implode(',', $actions)
				]);
			})
			->exists();

		if ($exists) {

			/*
			Log::info("ML Notificación duplicada", [
				'resource' => $resource,
				'actions'  => $actions
			]);
			*/


			return response()->json(['status' => 'duplicate'], 200);
		}
		// Obtener al usuario para saber qué CLIENT_ID utiliza
		$usuario = MLCLient::with('cliente')->where('meli_user_id', $userId)->first();

		if (! $usuario) {
			return response()->json(['error' => 'Usuario ML no encontrado'], 404);
		}

		// Guardar registro
		$notificacion = MLNotificacion::create([
			'topic'         => $topic,
			'actions'       => implode(',', $actions),
			'resource'      => $resource,
			'user_id'       => $payload['user_id'] ?? null,
			'application_id' => $payload['application_id'] ?? null,
			'attempts'      => $payload['attempts'] ?? 1,
			'payload'       => $payload,
			'sent_at'       => isset($payload['sent']) ? now()->parse($payload['sent']) : now(),
			'status'        => 'received',
		]);

		Log::info('ML Notification stored', [
			'id'       => $notificacion->id,
			'resource' => $resource,
			'actions'  => $actions
		]);
		// Enviar a cola (pasamos el modelo, no el payload suelto)
		ProcessMercadoLibreNotification::dispatch($payload)
			->onQueue('meli');

		return response()->json(['status' => 'ok'], 200);
	}

}
