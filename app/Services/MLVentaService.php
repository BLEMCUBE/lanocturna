<?php

namespace App\Services;

use App\Models\MercadoLibreCliente;
use App\Services\ListaUsuarioService;
use App\Services\MercadoLibreService;
use App\Models\MercadoLibreVenta;
use App\Models\MercadoLibreListaUsuario;

class MLVentaService
{
	public function __construct(
		private MercadoLibreService $ml,
		private ListaUsuarioService $listaUsuarioService,
	) {}
	public function updateOrCreate($item)
	{
		$cliente = MercadoLibreCliente::with('usuario')->first();
		$row = MercadoLibreVenta::where('mercadolibre_venta_id', '=', $item['id'])->first();
		//existComprador
		$existComprador = MercadoLibreListaUsuario::where('user_id', $payload['buyer']['id'] ?? null)->exists();

		if (!$existComprador) {
			$itemUser = $this->ml->apiGet('/users/' . $item['buyer']['id'], $cliente->usuario->meli_user_id);
			$this->listaUsuarioService->updateOrCreate($itemUser);
		}
		//existVendedor
		$existVendedor = MercadoLibreListaUsuario::where('user_id', $payload['seller']['id'] ?? null)->exists();

		if (!$existVendedor) {
			$itemUser = $this->ml->apiGet('/users/' . $item['seller']['id'], $cliente->usuario->meli_user_id);
			$this->listaUsuarioService->updateOrCreate($itemUser);
		}
		if ($row === null) {

			//ids items
			$data =	MercadoLibreVenta::updateOrCreate(
				['mercadolibre_venta_id' => $item['id']],
				[
					'mercadolibre_venta_id' => $item['id'] ?? null,
					'buyer_id' => $item['buyer']['id'] ?? null,
					'seller_id' => $item['seller']['id'] ?? null,
					'status' => $item['status'] ?? 'pending',
					'payload' => $item,
					'item_ids' => collect($item['order_items'])
						->pluck('item.id')
						->filter()
						->values()
						->toArray(),
				]
			);
			return $data;
		}
		return null;
	}
}
