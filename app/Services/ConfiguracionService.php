<?php

namespace App\Services;
use App\Models\Producto;

class ConfiguracionService
{
	public function getOp($arr, $key)
	{
		$dato = '';
		foreach ($arr as $ar) {
			if ($ar['slug'] === $key) {
				$dato = $ar['value'];
				break;
			}
		}
		return $dato;
	}


}
