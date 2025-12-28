<?php

namespace App\Services\MercadoLibre;

use App\Models\MLListaUsuario;

class ListaUsuarioService
{
	public function updateOrCreate($item)
	{
		$data =	MLListaUsuario::updateOrCreate(
			['user_id' => $item['id']],
			[
				'user_id' => $item['id'] ?? null,
				'payload' => $item,
			]
		);
		return $data;
	}
}
