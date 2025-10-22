<?php

namespace App\Http\Controllers\MercadoLibre;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\MercadoLibreCollection;
use App\Jobs\ProcessMercadoLibreNotification;
use App\Models\MercadoLibreCliente;
use App\Models\MercadoLibreUsuario;
use App\Models\MercadoLibreNotificacion;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;

class NotificacionController extends Controller
{
	public function indexClientes()
	{

		$items = new MercadoLibreCollection(
			MercadoLibreCliente::with('usuario')->orderBy('nombre', 'ASC')
				->get()
		);

		return Inertia::render('MercadoLibre/IndexClientes', [
			'items' => $items
		]);
	}


	public function conectar($id)
	{
		$client = MercadoLibreCliente::findOrFail($id);

		$query = http_build_query([
			'response_type' => 'code',
			'client_id' => $client->client_id,
			'redirect_uri' => route('mercadolibre.callback'),
			'state' => $client->id, // pasamos el ID del cliente
		]);

		return redirect("https://auth.mercadolibre.com.uy/authorization?$query");
	}


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
			'redirect_uri' => route('mercadolibre.callback'),
		]);

		$tokenData = $tokenResponse->json();

		if (isset($tokenData['error'])) {
			return redirect()->route('mercadolibre.index-clientes')->with('error', 'Error al obtener el token');
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
		]);

		return redirect()->route('mercadolibre.index-clientes')->with('success', 'Cuenta de Mercado Libre vinculada correctamente');
	}

	public function desconectar(MercadoLibreCliente $cliente)
	{
		// Eliminar usuario asociado 1 a 1
		if ($cliente->usuario) {
			$cliente->usuario->delete();
		}
		return redirect()->back()->with('success', 'Cuenta de Mercado Libre desconectada correctamente.');
	}

	public function notifications(Request $request)
	{
		http_response_code(200);
		flush();
		$payload = $request->all();

		// Validar datos mínimos
		if (empty($payload['resource']) || empty($payload['topic'])) {
			Log::warning('⚠️ Notificación incompleta recibida', ['payload' => $payload]);
			return response()->json(['message' => 'Datos incompletos'], 400);
		} else {
			// ✅ Guardar notificación
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
				'status'        => 'pending',
			]);
			//guardar en log
			Log::info('Notificación Mercado Libre:', ['payload' => $payload]);
			//enviar a cola
			ProcessMercadoLibreNotification::dispatch($payload)->onQueue('meli');
		}
	}
}
