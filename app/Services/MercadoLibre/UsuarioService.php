<?php

namespace App\Services\MercadoLibre;

use App\Models\MLListaUsuario;
use App\Traits\BaseMLService;

class UsuarioService
{

	use BaseMLService;

	public function __construct(protected MercadoLibreService $ml) {}

	public function buscarUsuario(string $userId,$clientId)
	{
		$exists = MLListaUsuario::where('user_id', $userId)->first();
		if ($exists) return $exists;


		$this->forClient($clientId);

		$meliUser = $this->usuarioMeliId();
		$data = $this->mlForClient()->apiGet('/users/' . $userId, $meliUser);
		// asume ListaUsuarioService existe y guarda el usuario
		//return app(ListaUsuarioService::class)->updateOrCreate($data, $this->clienteId());
		return app(ListaUsuarioService::class)->updateOrCreate($data);
	}

}
