<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\MercadoLibre\MercadoLibreService;
use App\Models\MLReclamo;
use App\Services\MercadoLibre\ReclamoService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FetchUnreadReclamosJob implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	public ?string $clientId;
	public ?string $meliUserId;
	public ?string $resourceId;

	public function __construct(
		?string $clientId = null,
		?string $meliUserId = null,
		?string $resourceId = null
	) {
		$this->clientId   = $clientId;
		$this->meliUserId = $meliUserId;
		$this->resourceId = $resourceId;
	}

	public function handle()
	{
		if (!$this->clientId) return;

		$ml = app(MercadoLibreService::class)->forClient($this->clientId);

		// Obtener reclamos activos
		$oldReclamos = MLReclamo::where('status', '!=', 'closed')
			->where('meli_user_id', '=', $this->clientId)
			->get();

		if ($oldReclamos->isEmpty()) return;

		foreach ($oldReclamos as $reclamo) {
			$oldUpdated = Carbon::parse($reclamo->last_updated)->format('Y-m-d H:i');
			if ($oldUpdated === null) {
				// Si no hay fecha, actualizar siempre
				$this->fetchAndUpdateReclamo($ml, $reclamo->reclamo_id);
				continue;
			}

			// Obtener información actualizada del reclamo
			$response = $ml->apiGetDos('/post-purchase/v1/claims/' . $reclamo->reclamo_id, $this->meliUserId);
			$item = $response['body'] ?? null;

			if ($item === null) continue;

			// Comparar fechas de actualización
			$newLastUpdated = Carbon::parse($item['last_updated'])->format('Y-m-d H:i') ?? null;

			if ($this->shouldUpdate($oldUpdated, $newLastUpdated)) {
				Log::info("Actualizando reclamo {$reclamo->reclamo_id}", [
					'old' => $oldUpdated,
					'new' => $newLastUpdated
				]);


				app(ReclamoService::class)->updateOrCreate($item, $this->clientId);
			} else {
				/*
				Log::debug("Reclamo {$reclamo->reclamo_id} no requiere actualización", [
					'last_updated_bd' => $oldUpdated,
					'last_updated_api' => $newLastUpdated
				]);*/
			}

			// Opcional: Actualizar mensajes solo si hubo cambios
			// app(ReclamoService::class)->mensajes($item['id'], $this->clientId);
		}
	}

	/**
	 * Determina si se debe actualizar el reclamo
	 */
	private function shouldUpdate(?string $oldUpdated, ?string $newLastUpdated): bool
	{
		// Si no hay fecha nueva, no actualizar
		if ($newLastUpdated === null) return false;

		// Si no había fecha vieja, actualizar
		if ($oldUpdated === null) return true;

		// Convertir a objetos Carbon para comparación precisa
		try {
			$oldDate = Carbon::parse($oldUpdated);
			$newDate = Carbon::parse($newLastUpdated);

			// Actualizar solo si la fecha nueva es más reciente
			return $newDate->greaterThan($oldDate);
		} catch (\Exception $e) {
			Log::error("Error al comparar fechas", [
				'old' => $oldUpdated,
				'new' => $newLastUpdated,
				'error' => $e->getMessage()
			]);

			// Si hay error en el parseo, actualizar por precaución
			return true;
		}
	}

	/**
	 * Método auxiliar para obtener y actualizar un reclamo
	 */
	private function fetchAndUpdateReclamo($ml, string $reclamoId): void
	{
		$response = $ml->apiGetDos('/post-purchase/v1/claims/' . $reclamoId, $this->meliUserId);
		$item = $response['body'] ?? null;

		if ($item !== null) {
			app(ReclamoService::class)->updateOrCreate($item, $this->clientId);
		}
	}
}
