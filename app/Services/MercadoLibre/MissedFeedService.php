<?php

namespace App\Services\MercadoLibre;

use App\Models\MLNotificacion;
use App\Traits\BaseMLService;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Facades\Http;
class MissedFeedService
{
	use BaseMLService;

	protected $topics = [
		"messages",
		"questions",
		"post_purchase",
		//"orders_v2",
		//"items",
		//"orders_feedback",
		//"payments",
		//"shipments"
	];
	protected $ml;
	protected int $limit = 200;
	public function __construct(
		MercadoLibreService $ml
	) {
		$this->ml = $ml;
	}

	/**
	 * Sincroniza todos los topics para el cliente actual
	 */
	public function syncAllTopics($clientId)
	{
		$this->ml->forClient($clientId);
		$this->forClient($clientId);
		foreach ($this->topics as $topic) {
			$this->syncTopic($topic);
		}
	}

	/**
	 * Sincroniza un topic específico con paginación
	 */
	public function syncTopic(string $topic): void
	{
		if (!$this->clienteId()) {
			throw new \Exception("Cliente no inicializado. Usa forClient() primero.");
		}

		$offset = 0;

		while (true) {
			$response = $this->getMissedFeeds($topic, $offset, $this->limit);

			if (empty($response['messages'])) {
				break;
			}

			foreach ($response['messages'] as $notification) {
				Log::info('notts', ['nott' => $notification]);
				//$this->saveNotification($topic, $notification);
			}

			if (count($response['messages']) < $this->limit) {
				break;
			}

			$offset += $this->limit;
		}
	}

	/**
	 * Llamada a la API ML de missed feeds
	 */
	protected function getMissedFeeds(string $topic, int $offset, int $limit): array
	{

		$appId = $this->clienteId();
		$accessToken = $this->ml->getAccessToken($this->usuarioMeliId());

		$url = "https://api.mercadolibre.com/missed_feeds";

		try {
			$res = Http::withToken($accessToken)->get($url, [
				'app_id' => $appId,
				'topic' => $topic,
				'limit' => $limit,
				'offset' => $offset,
			]);

			if ($res->failed()) {
				Log::error("MissedFeeds ML error ({$topic}): " . $res->body());
				return [];
			}

			return $res->json();
		} catch (\Exception $e) {
			Log::error("MissedFeeds ML exception ({$topic}): " . $e->getMessage());
			return [];
		}
	}

	/**
	 * Guarda la notificación en BD evitando duplicados
	 */
	protected function saveNotification(string $topic, array $notification): void
	{
		MLNotificacion::updateOrCreate(
			[
				'topic' => $topic,
				'resource' => $notification['resource'] ?? null,
				'client_id' => $this->clienteId(),
			],
			[
				'user_id' => $notification['user_id'] ?? null,
				'application_id' => $notification['application_id'] ?? null,
				'payload' => json_encode($notification),
			]
		);
	}
}
