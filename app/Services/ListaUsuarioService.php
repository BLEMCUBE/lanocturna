<?php

namespace App\Services;

use App\Models\MercadoLibreListaUsuario;

class ListaUsuarioService
{
	public function updateOrCreate($item)
	{
		$data =	MercadoLibreListaUsuario::updateOrCreate(
			['user_id' => $item['id']],
			[
				'user_id' => $item['id'] ?? null,
				'payload' => $item,
			]
		);
		return $data;
	}
}
