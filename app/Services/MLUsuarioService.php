<?php

namespace App\Services;

use App\Models\MercadoLibreCliente;
use App\Services\ListaUsuarioService;
use App\Services\MercadoLibreService;
use App\Models\MercadoLibreListaUsuario;

class MLUsuarioService
{
	public function __construct(
		private MercadoLibreService $ml,
		private ListaUsuarioService $listaUsuarioService,
	) {}


	public function buscarUsuario($id)
	{
		$user = $this->datosUsuario();
		$existeUser = MercadoLibreListaUsuario::where('user_id', $id ?? null)->exists();
		if (!$existeUser) {
			$itemUser = $this->ml->apiGet('/users/' . $id, $user->meli_user_id);
			$data = $this->listaUsuarioService->updateOrCreate($itemUser);
		} else {
			$data = $existeUser;
		}
		return $data;
	}

	public function datosUsuario()
	{
		$cliente = MercadoLibreCliente::where('is_default', '=', 1)->with('usuario')->first();

		return $cliente->usuario;
	}
}
