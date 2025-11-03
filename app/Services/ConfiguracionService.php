<?php

namespace App\Services;

use App\Models\Configuracion;

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

	public function getOption($key)
	{
		$config = Configuracion::get();
		$dato = '';
		foreach ($config as $ar) {
			if ($ar['slug'] === $key) {
				$dato = $ar['value'];
				break;
			}
		}
		return $dato;
	}
}
