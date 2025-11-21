<?php

namespace App\Http\Controllers;

use App\Services\MercadoLibreService;
use Illuminate\Http\Request;
use App\Services\VentaService;

class VentaWebController extends Controller
{

	public function __construct(
		private VentaService $ventaService,
		private	MercadoLibreService $ml,

	) {}


	public function store(Request $request)
	{
		$venta = $this->ventaService->crearEnvio($request);

		if ($venta['estado'] === true) {
			if ($venta['destino'] === 'ENVIO FLASH') {
				$this->ml->pusherNotificacion('venta', 'envio');
			}
		}
		return response()->json(
			$venta
		);
	}
}
