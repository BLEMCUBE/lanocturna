<?php

namespace App\Http\Controllers\MercadoLibre;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Jobs\ProcessMercadoLibreNotification;
use App\Models\MercadoLibreCliente;
use App\Models\MercadoLibreUsuario;
use App\Models\MercadoLibreNotificacion;
use Illuminate\Support\Facades\Http;

class NotificacionController extends Controller
{
	public function callback(Request $request)
	{
		$code = $request->get('code');
		$cliente_id = $request->get('state');
		$client = MercadoLibreCliente::findOrFail($cliente_id);
		// Intercambiar code por access_token
		$tokenResponse = Http::asForm()->post('https://api.mercadolibre.com/oauth/token', [
			'grant_type' => 'authorization_code',
			'client_id' => $client->client_id,
			'client_secret' => $client->client_secret,
			'code' => $code,
			//'scope'=>"read%20write%20offline_access%20orders%20items%20messages%20shipments",
			'redirect_uri' => route('mercadolibre.callback'),
		]);

		$tokenData = $tokenResponse->json();

		if (isset($tokenData['error'])) {
			return redirect()->route('mercadolibre.clientes.index')->with('error', 'Error al obtener el token');
		}

		$accessToken = $tokenData['access_token'];
		// Obtener datos del usuario de Mercado Libre
		$userResponse = Http::withToken($accessToken)->get('https://api.mercadolibre.com/users/me');
		$meliUser = $userResponse->json();
		// Guardar o actualizar el usuario 1 a 1
		$usuario = MercadoLibreUsuario::updateOrCreate(
			['cliente_id' => $client->id], // garantizamos 1 a 1
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
		return redirect()->route('mercadolibre.clientes.index')->with('success', 'Cuenta de Mercado Libre vinculada correctamente');
	}

	public function notifications(Request $request)
	{

		$payload = $request->all();
		$resource = $payload['resource'] ?? null;
		$topic = $payload['topic'] ?? null;

		if (!$resource) {
			return response()->json(['error' => 'Notificación sin resource'], 400);
		}


		// Evitar procesar duplicados
		$exists = MercadoLibreNotificacion::where('resource', $resource)->exists();

		if ($exists) {
			// Ya procesada: responder OK sin hacer nada
			Log::info("Notificación duplicada: {$resource}");
			return response()->json(['status' => 'duplicate'], 200);
		}

		// Guardar registro como "recibido"
		MercadoLibreNotificacion::create([
			'topic'         => $payload['topic'] ?? null,
			'actions'       => is_array($payload['actions'] ?? null)
				? implode(',', $payload['actions'])
				: ($payload['actions'] ?? null),
			'resource'      => $payload['resource'] ?? null,
			'user_id'       => $payload['user_id'] ?? null,
			'application_id' => $payload['application_id'] ?? null,
			'attempts'      => $payload['attempts'] ?? 1,
			'payload'       => $payload,
			'sent_at'       => isset($payload['sent']) ? now()->parse($payload['sent']) : now(),
			'status'        => 'received',
		]);
		//guardar en log
		Log::info('Notificación Mercado Libre:', ['payload' => $payload]);
		//enviar a cola
		ProcessMercadoLibreNotification::dispatch($payload)->onQueue('meli');
		//}
		// Siempre devolver 200 OK rápido
		return response()->json(['status' => 'ok'], 200);
	}
}
