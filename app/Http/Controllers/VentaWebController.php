<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use App\Services\ConfiguracionService;
use Pusher\Pusher;
use Illuminate\Http\Request;
use App\Services\VentaService;

class VentaWebController extends Controller
{

	public function __construct(
		private VentaService $ventaService,
		private ConfiguracionService $configuracionService

	) {}


	public function store(Request $request)
	{

		$configuracion = Configuracion::get();
		$venta = $this->ventaService->crearEnvio($request);
		$options = [
			'cluster' => $this->configuracionService->getOp($configuracion, 'pusher-cluster'),
			'useTLS' => $this->configuracionService->getOp($configuracion, 'pusher-forcetls'),
		];

		$data = [
			'id' => $this->configuracionService->getOp($configuracion, 'pusher-id'),
			'key' => $this->configuracionService->getOp($configuracion, 'pusher-key'),
			'forcetls' => $this->configuracionService->getOp($configuracion, 'pusher-forcetls'),
			'secret' => $this->configuracionService->getOp($configuracion, 'pusher-secret')
		];

		$pusher = new Pusher(
			$data['key'],
			$data['secret'],
			$data['id'],
			$options
		);

		if ($venta['estado'] === true) {
			if ($venta['destino'] === 'ENVIO FLASH') {
				$pusher->trigger('venta', 'envio', $venta);
			}
		}
		return response()->json(
			$venta
		);
	}
}
