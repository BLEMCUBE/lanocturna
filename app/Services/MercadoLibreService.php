<?php

namespace App\Services;

use App\Models\MercadoLibreCliente;
use App\Models\MercadoLibreUsuario;
use App\Models\MercadoLibreNotificacion;
use App\Services\ConfiguracionService;
use Pusher\Pusher;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\ConnectionException;
use Exception;

class MercadoLibreService
{
	protected $apiBase = 'https://api.mercadolibre.com';
	protected $oauthUrl = 'https://api.mercadolibre.com/oauth/token';
	public function __construct(
		private ConfiguracionService $configuracionService,
	) {}
	/**
	 * Obtiene el token del usuario desde la BD y lo refresca si expiró.
	 */
	public function getAccessToken($userId = null)
	{
		$usuario = MercadoLibreUsuario::when($userId, fn($q) => $q->where('meli_user_id', $userId))
			->orderByDesc('id')
			->first();
		if (!$usuario) {
			throw new Exception('Usuario de Mercado Libre no encontrado.');
		}
		$cliente = MercadoLibreCliente::where('id', '=', $usuario->cliente_id)->first();
		$time = config('app.timezone');
		$fActual = Carbon::now($time)->format('Y-m-d H:i:s');
		$fExpira = Carbon::create($usuario->expires_at)->format('Y-m-d H:i:s');
		$fE = Carbon::parse($fExpira);
		$fA = Carbon::parse($fActual);
		if ($fE < $fA) {
			Log::info('Token expirado, refrescando...', ['meli_user_id' => $usuario->meli_user_id]);

			$response = Http::asForm()->post($this->oauthUrl, [
				'grant_type'    => 'refresh_token',
				'client_id'     => $cliente->client_id,
				'client_secret' => $cliente->client_secret,
				'refresh_token' => $usuario->refresh_token,
			]);

			if ($response->failed()) {
				Log::error('Error al refrescar token ML', ['response' => $response->body()]);
				throw new Exception('No se pudo refrescar el token: ' . $response->body());
			}

			$data = $response->json();

			$usuario->update([
				'access_token'  => $data['access_token'],
				'refresh_token' => $data['refresh_token'] ?? $usuario->refresh_token,
				'expires_at' => now()->addSeconds($tokenData['expires_in'] ?? 21600),
			]);
		}
		return $usuario->access_token;
	}

	public function actualizar($resource)
	{
		$notif = MercadoLibreNotificacion::where('resource', '=', $resource)->first();
		if (!is_null($notif)) {
			$notif->update([
				'status' => 'processed'
			]);
		}
	}

	/**
	 * 🔄 Refrescar token de acceso (usando refresh_token)
	 */
	public function refreshAccessToken($userId)
	{
		Log::info("Refrescando token Mercado Libre para usuario {$userId}");

		$usuario = MercadoLibreUsuario::when($userId, fn($q) => $q->where('meli_user_id', $userId))
			->first();
		if (!$usuario || !$usuario->refresh_token) {
			throw new Exception("No se encontró refresh_token para el usuario {$userId}");
		}
		$cliente = MercadoLibreCliente::where('id', '=', $usuario->cliente_id)->first();

		try {
			$response = Http::asForm()->post($this->oauthUrl, [
				'grant_type' => 'refresh_token',
				'client_id'     => $cliente->client_id,
				'client_secret' => $cliente->client_secret,
				'refresh_token' => $usuario->refresh_token,
			]);

			if ($response->failed()) {
				Log::error('Error al refrescar token Mercado Libre', [
					'user_id' => $userId,
					'body' => $response->body(),
				]);
				throw new Exception("Error al refrescar token Mercado Libre: {$response->body()}");
			}

			$data = $response->json();

			// Guardar nuevos tokens en BD
			$usuario->update([
				'access_token' => $data['access_token'],
				'refresh_token' => $data['refresh_token'] ?? $usuario->refresh_token,
				'expires_at' => now()->addSeconds($tokenData['expires_in'] ?? 21600),
			]);
			$cliente->update([
				'redirect_uri' => route('mercadolibre.callback')
			]);

			Log::info("Token de acceso actualizado correctamente para usuario {$userId}");

			return $data['access_token'];
		} catch (ConnectionException $e) {
			Log::error('Error de conexión al refrescar token Mercado Libre', [
				'user_id' => $userId,
				'message' => $e->getMessage(),
			]);
			throw new Exception("No se pudo conectar con Mercado Libre: {$e->getMessage()}");
		}
	}

	public function apiGet($endpoint, $userId = null, $params = [])
	{
		$token = $this->getAccessToken($userId);

		try {
			$response = Http::withToken($token)
				->acceptJson()
				->timeout(30)
				->connectTimeout(10)
				->retry(3, 200)
				->withOptions(['verify' => true])
				->get("{$this->apiBase}{$endpoint}", $params);

			// Si el token expiró, intenta renovarlo
			if ($response->status() === 401) {
				$token = $this->refreshAccessToken($userId);
				return $this->apiGet($endpoint, $userId, $params);
			}

			if ($response->failed()) {
				Log::error('Error GET ML', [
					'endpoint' => $endpoint,
					'params'   => $params,
					'status'   => $response->status(),
					'body'     => $response->body(),
				]);
				throw new Exception("Error GET a Mercado Libre: {$response->body()}");
			}

			return $response->json();
		} catch (ConnectionException $e) {
			Log::error('Error de conexión GET ML', [
				'endpoint' => $endpoint,
				'params'   => $params,
				'message'  => $e->getMessage(),
			]);
			throw new Exception("Error de conexión con Mercado Libre: {$e->getMessage()}");
		}
	}

	public function apiGetDos($endpoint, $userId = null, $params = [])
	{
		$token = $this->getAccessToken($userId);
		$url   = "{$this->apiBase}{$endpoint}";

		try {
			$response = Http::withToken($token)
				->acceptJson()
				->timeout(60)          // más seguro para órdenes grandes
				->connectTimeout(10)
				->retry(3, 200)
				->withOptions(['verify' => true])
				->get($url, $params);

			// ⚠️ Si el token expiró, intenta renovarlo una sola vez
			if ($response->status() === 401) {
				$token = $this->refreshAccessToken($userId);

				$response = Http::withToken($token)
					->acceptJson()
					->timeout(60)
					->connectTimeout(10)
					->retry(3, 200)
					->get($url, $params);
			}

			$isSuccess = $response->successful();

			$result = [
				'success'     => $isSuccess,
				'status_code' => $response->status(),
				'error'       => $response->json('error') ?? null,
				'message'     => $response->json('message') ?? null,
				'body'        => $response->json(),
				'raw'         => $response->body(),
			];

			if (! $isSuccess) {
				Log::error('Error GET2 ML', [
					'endpoint' => $endpoint,
					'params'   => $params,
					'result'   => $result,
				]);
			}

			return $result;
		}

		// ❗ Captura errores de conexión (cURL error 28, DNS, timeout, etc.)
		catch (ConnectionException $e) {
			$error = [
				'success'     => false,
				'status_code' => 0,
				'error'       => 'connection_error',
				'message'     => $e->getMessage(),
			];

			Log::error('Error de conexión GET ML', [
				'endpoint' => $endpoint,
				'params'   => $params,
				'error'    => $error,
			]);

			return $error;
		}

		// ❗ Captura cualquier otro error inesperado
		catch (\Throwable $e) {
			$error = [
				'success'     => false,
				'status_code' => 0,
				'error'       => 'unexpected_error',
				'message'     => $e->getMessage(),
				//'trace'       => $e->getTraceAsString(),
			];

			Log::error('Excepción GET ML', [
				'endpoint' => $endpoint,
				'params'   => $params,
				'error'    => $error,
			]);

			return $error;
		}
	}


	/**
	 * POST genérico
	 */
	public function apiPost($endpoint, $data = [], $userId = null)
	{
		$token = $this->getAccessToken($userId);

		try {
			$response = Http::withToken($token)
				->acceptJson()
				->timeout(30)
				->connectTimeout(10)
				->retry(3, 200)
				->withOptions(['verify' => true])
				->post("{$this->apiBase}{$endpoint}", $data);

			if ($response->status() === 401) {
				$token = $this->refreshAccessToken($userId);
				return $this->apiPost($endpoint, $data, $userId);
			}

			if ($response->failed()) {
				Log::error('Error POST ML', [
					'endpoint' => $endpoint,
					'data'     => $data,
					'status'   => $response->status(),
					'body'     => $response->body(),
				]);
				throw new Exception("Error POST a Mercado Libre: {$response->body()}");
			}

			return $response->json();
		} catch (ConnectionException $e) {
			Log::error('Error de conexión POST ML', [
				'endpoint' => $endpoint,
				'data'     => $data,
				'message'  => $e->getMessage(),
			]);
			throw new Exception("Error de conexión con Mercado Libre: {$e->getMessage()}");
		}
	}

	/**
	 * PUT genérico
	 */
	public function apiPut($endpoint, $data = [], $userId = null)
	{
		$token = $this->getAccessToken($userId);

		try {
			$response = Http::withToken($token)
				->acceptJson()
				->timeout(30)
				->connectTimeout(10)
				->retry(3, 200)
				->withOptions(['verify' => true])
				->put("{$this->apiBase}{$endpoint}", $data);

			if ($response->status() === 401) {
				$token = $this->refreshAccessToken($userId);
				return $this->apiPut($endpoint, $data, $userId);
			}

			if ($response->failed()) {
				Log::error('Error PUT ML', [
					'endpoint' => $endpoint,
					'data'     => $data,
					'status'   => $response->status(),
					'body'     => $response->body(),
				]);
				throw new Exception("Error PUT a Mercado Libre: {$response->body()}");
			}

			return $response->json();
		} catch (ConnectionException $e) {
			Log::error('Error de conexión PUT ML', [
				'endpoint' => $endpoint,
				'data'     => $data,
				'message'  => $e->getMessage(),
			]);
			throw new Exception("Error de conexión con Mercado Libre: {$e->getMessage()}");
		}
	}

	/**
	 * DELETE genérico
	 */
	public function apiDelete($endpoint, $userId = null)
	{
		$token = $this->getAccessToken($userId);

		try {
			$response = Http::withToken($token)
				->acceptJson()
				->timeout(30)
				->connectTimeout(10)
				->retry(3, 200)
				->withOptions(['verify' => true])
				->delete("{$this->apiBase}{$endpoint}");

			if ($response->status() === 401) {
				$token = $this->refreshAccessToken($userId);
				return $this->apiDelete($endpoint, $userId);
			}

			if ($response->failed()) {
				Log::error('Error DELETE ML', [
					'endpoint' => $endpoint,
					'status'   => $response->status(),
					'body'     => $response->body(),
				]);
				throw new Exception("Error DELETE a Mercado Libre: {$response->body()}");
			}

			return $response->json();
		} catch (ConnectionException $e) {
			Log::error('Error de conexión DELETE ML', [
				'endpoint' => $endpoint,
				'message'  => $e->getMessage(),
			]);
			throw new Exception("Error de conexión con Mercado Libre: {$e->getMessage()}");
		}
	}

	public function pusherNotificacion($canal, $evento, $data = 'mensaje')
	{
		$options = [
			'cluster' => $this->configuracionService->getOption('pusher-cluster'),
			'useTLS' => $this->configuracionService->getOption('pusher-forcetls'),
		];

		$data = [
			'id' => $this->configuracionService->getOption('pusher-id'),
			'key' => $this->configuracionService->getOption('pusher-key'),
			'forcetls' => $this->configuracionService->getOption('pusher-forcetls'),
			'secret' => $this->configuracionService->getOption('pusher-secret')
		];

		$pusher = new Pusher(
			$data['key'],
			$data['secret'],
			$data['id'],
			$options
		);
		$pusher->trigger($canal, $evento, $data);
	}
}
