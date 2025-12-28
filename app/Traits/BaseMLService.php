<?php

namespace App\Traits;

use App\Models\MLApp;

trait BaseMLService
{
	protected ?MLApp $clienteActual = null;


	public function forClient(string $clientId)
	{
		if (empty($clientId)) {
			throw new \Exception("ClientId vacío en BaseMLService");
		}

		$this->clienteActual = MLApp::where('app_id', $clientId)->first();

		if (! $this->clienteActual) {
			throw new \Exception("Cliente ML no encontrado: {$clientId}");
		}

		return $this;
	}
	protected function mlForClient()
	{
		if (! $this->clienteActual) {
			throw new \Exception("Cliente no inicializado. Debes llamar a forClient() antes.");
		}

		return app(\App\Services\MercadoLibre\MercadoLibreService::class)
			->forClient($this->clienteActual->app_id);
	}

	protected function clienteId(): ?string
	{
		return $this->clienteActual?->app_id;
	}

	protected function usuarioMeliId(): ?string
	{
		return $this->clienteActual?->usuario?->meli_user_id;
	}
}
