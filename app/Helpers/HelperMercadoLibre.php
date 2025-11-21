<?php

namespace App\Helpers;

class HelperMercadoLibre
{
	private static $TIPOS = [
		[
			'site_id' => 'MLU',
			'id' => 'gold_special',
			'name' => 'Premium',
			'remaining_listings' => null
		],
		[
			'site_id' => 'MLU',
			'id' => 'bronze',
			'name' => 'Clásica',
			'remaining_listings' => null
		]
	];

	private static $DEPARTAMENTOS = [
		['nombre' => 'Artigas', 'codigo' => 'UY-AR'],
		['nombre' => 'Canelones', 'codigo' => 'UY-CA'],
		['nombre' => 'Cerro Largo', 'codigo' => 'UY-CL'],
		['nombre' => 'Colonia', 'codigo' => 'UY-CO'],
		['nombre' => 'Durazno', 'codigo' => 'UY-DU'],
		['nombre' => 'Flores', 'codigo' => 'UY-FS'],
		['nombre' => 'Florida', 'codigo' => 'UY-FD'],
		['nombre' => 'Lavalleja', 'codigo' => 'UY-LA'],
		['nombre' => 'Maldonado', 'codigo' => 'UY-MA'],
		['nombre' => 'Montevideo', 'codigo' => 'UY-MO'],
		['nombre' => 'Paysandu', 'codigo' => 'UY-PA'],
		['nombre' => 'Río Negro', 'codigo' => 'UY-RN'],
		['nombre' => 'Rivera', 'codigo' => 'UY-RV'],
		['nombre' => 'Rocha', 'codigo' => 'UY-RO'],
		['nombre' => 'Salto', 'codigo' => 'UY-SA'],
		['nombre' => 'San José', 'codigo' => 'UY-SJ'],
		['nombre' => 'Soriano', 'codigo' => 'UY-SO'],
		['nombre' => 'Tacuarembó', 'codigo' => 'UY-TA'],
		['nombre' => 'Treinta y Tres', 'codigo' => 'UY-TT']
	];

	public static function tipo($tipo)
	{
		//laravel
		return collect(self::$TIPOS)
			->firstWhere('id', $tipo)['name'] ?? null;
	}

	public static function departamento($codigo)
	{
		//php
		$indice = array_search($codigo, array_column(self::$DEPARTAMENTOS, 'codigo'));
		return $indice !== false ? self::$DEPARTAMENTOS[$indice]['nombre'] : null;
	}
}
