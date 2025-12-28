<?php

namespace App\Services\MercadoLibre;

use App\Models\MLEnvio;
use App\Traits\BaseMLService;

class EnvioService
{
	use BaseMLService;

	public function __construct(
		private MercadoLibreService $ml,

	) {}

	/**
	 * Crear o actualizar reclamo
	 */
	public function guardarActualizar($item, $clientId)
	{
		$this->forClient($clientId);
		$meli_user_id = $this->usuarioMeliId();
		if (!$meli_user_id) return;

		$reclamo = MLEnvio::updateOrCreate(
			['envio_id' => $item['id']],
			[
				'envio_id' => $item['id'],
				'orden_id'     => $item['order_id'] ?? null,
				'estado'     => $item['status'] ?? null,
				'modo_envio'     => $item['mode'] ?? null,
				'nro_rastreo'     => $item['tracking_number'] ?? null,
				'fecha_envio'     => $item['status_history']['date_shipped'] ?? null,
				'fecha_entrega'     => $item['status_history']['date_delivered'] ?? null,
				'payload'     => $item,
			]
		);

		return $reclamo;
	}

		public function guardarCosto($envioId,$item, $clientId)
	{
		$this->forClient($clientId);
		$meli_user_id = $this->usuarioMeliId();
		if (!$meli_user_id) return;

		$reclamo = MLEnvio::updateOrCreate(
			['envio_id' => $envioId],
			[
				'costo'     => $item,
			]
		);
		return $reclamo;
	}
}
