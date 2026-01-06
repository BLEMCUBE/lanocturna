<?php

namespace App\Services\MercadoLibre;

use App\Models\MLApp;
use App\Models\MLClient;
use App\Models\MLNotificacion;
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
	protected $clienteActual = null;

	public function __construct(
		private ConfiguracionService $configuracionService,
	) {}

	/**
	 * Asigna el cliente que se usará en todas las llamadas.
	 */
	public function forClient(string $clientId)
	{
		if (empty($clientId)) {
			throw new Exception("clientId vacío en MercadoLibreService");
		}

		$this->clienteActual = MLApp::where('app_id', $clientId)->first();

		if (! $this->clienteActual) {
			throw new Exception("MLApp no encontrado para app_id={$clientId}");
		}

		return $this;
	}
	/**
	 * Obtiene el token de un usuario y refresca si expiró.
	 */
	public function getAccessToken($meliUserId)
	{
		if (! $this->clienteActual) {
			throw new Exception("Debes llamar a forClient(app_id) antes de getAccessToken()");
		}

		$usuario = MLClient::where('meli_user_id', $meliUserId)
			->where('app_id', $this->clienteActual->id)
			->first();

		if (! $usuario) {
			throw new Exception("Usuario ML no encontrado para app_id={$this->clienteActual->app_id}");
		}

		// Validar expiración
		if (Carbon::parse($usuario->expires_at)->isPast()) {

			Log::info('Token expirado, refrescando...', [
				'meli_user_id' => $meliUserId,
				'client_id' => $this->clienteActual->app_id
			]);

			$response = Http::asForm()->post($this->oauthUrl, [
				'grant_type'    => 'refresh_token',
				'client_id'     => $this->clienteActual->app_id,
				'client_secret' => $this->clienteActual->client_secret,
				'refresh_token' => $usuario->refresh_token,
			]);

			if ($response->failed()) {
				throw new Exception("No se pudo refrescar el token: " . $response->body());
			}

			$data = $response->json();

			$usuario->update([
				'access_token'  => $data['access_token'],
				'refresh_token' => $data['refresh_token'] ?? $usuario->refresh_token,
				'expires_at'    => now()->addSeconds($data['expires_in'] ?? 21600),
			]);
		}

		return $usuario->access_token;
	}


	public function actualizar($resource,$action): void
	{
		$notif = MLNotificacion::where('resource', '=', $resource)
		->whereIn('actions', [$action])
		->first();
		if (!is_null($notif)) {
			$notif->update(['status' => 'processed']);
		}
	}

	/**
	 * Refrescar token manualmente (si 401).
	 */
	public function refreshAccessToken($meliUserId)
	{
		if (! $this->clienteActual) {
			throw new Exception("Debes llamar a forClient(app_id) antes de refreshAccessToken()");
		}

		$usuario = MLClient::where('meli_user_id', $meliUserId)
			->where('app_id', $this->clienteActual->id)
			->first();

		if (! $usuario) {
			throw new Exception("No se encontró el usuario para este app_id");
		}

		$response = Http::asForm()->post($this->oauthUrl, [
			'grant_type'    => 'refresh_token',
			'client_id'     => $this->clienteActual->app_id,
			'client_secret' => $this->clienteActual->client_secret,
			'refresh_token' => $usuario->refresh_token,
		]);

		if ($response->failed()) {
			throw new Exception("Error al refrescar token: " . $response->body());
		}

		$data = $response->json();

		$usuario->update([
			'access_token'  => $data['access_token'],
			'refresh_token' => $data['refresh_token'] ?? $usuario->refresh_token,
			'expires_at'    => now()->addSeconds($data['expires_in'] ?? 21600),
		]);

		return $data['access_token'];
	}

	/**
	 * GET genérico
	 */
	public function apiGet($endpoint, $meliUserId, $params = [])
	{
		try {
			$token = $this->getAccessToken($meliUserId);
			$response = Http::withToken($token)
				->acceptJson()
				->timeout(30)
				->connectTimeout(10)
				->retry(3, 200)
				->withOptions(['verify' => true])
				->get("{$this->apiBase}{$endpoint}", $params);

			// Si el token expiró, intenta renovarlo
			if ($response->status() === 401) {
				//$token = $this->refreshAccessToken($userId);
				//return $this->apiGet($endpoint, $userId, $params);
				$token = $this->refreshAccessToken($meliUserId);
				$response = Http::withToken($token)
					->acceptJson()
					->get($this->apiBase . $endpoint, $params);
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

	public function apiGetDos($endpoint, $meliUserId, $params = [])
	{


		try {
			$token = $this->getAccessToken($meliUserId);
			$url   = "{$this->apiBase}{$endpoint}";
			$response = Http::withToken($token)
				->acceptJson()
				->timeout(60)          // más seguro para órdenes grandes
				->connectTimeout(10)
				->retry(3, 200)
				->withOptions(['verify' => true])
				->get($url, $params);

			// ⚠️ Si el token expiró, intenta renovarlo una sola vez
			if ($response->status() === 401) {
				$token = $this->refreshAccessToken($meliUserId);

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
				//'url'     => $url,
				//'content_type' => $response->header('Content-Type'),
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

		// Captura cualquier otro error inesperado
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
	public function apiPost($endpoint, $data, $meliUserId)
	{
		$token = $this->getAccessToken($meliUserId);

		$response = Http::withToken($token)
			->acceptJson()
			->timeout(30)
			->connectTimeout(10)
			->retry(3, 200)
			->withOptions(['verify' => true])
			->post("{$this->apiBase}{$endpoint}", $data);

		if ($response->status() === 401) {
			$token = $this->refreshAccessToken($meliUserId);

			$response = Http::withToken($token)
				->acceptJson()
				->post($this->apiBase . $endpoint, $data);
		}

		if ($response->failed()) {
			throw new Exception("Error POST ML: " . $response->body());
		}

		return $response->json();
	}

	/**
	 * PUT genérico
	 */
	public function apiPut($endpoint, $data = [], $meliUserId = null)
	{
		$token = $this->getAccessToken($meliUserId);

		try {
			$response = Http::withToken($token)
				->acceptJson()
				->timeout(30)
				->connectTimeout(10)
				->retry(3, 200)
				->withOptions(['verify' => true])
				->put("{$this->apiBase}{$endpoint}", $data);

			if ($response->status() === 401) {
				$token = $this->refreshAccessToken($meliUserId);
				return $this->apiPut($endpoint, $data, $meliUserId);
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
				->timeout(60)
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
