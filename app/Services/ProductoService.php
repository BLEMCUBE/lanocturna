<?php

namespace App\Services;

use App\Models\Producto;

class ProductoService
{

	public function ProductoById($id)
	{

		$producto = Producto::findOrFail($id);

		return $producto;
	}

	public function ProductoBySku($sku)
	{
		$producto = Producto::where('origen', $sku)->first();

		return $producto;
	}


	public function ProductoEnvio($tipo_envio)
	{

		switch ($tipo_envio) {
			case 'Envío Flash':
				$code = 102;

				$envio = Producto::where('origen', $code)->first();

				if (!is_null($envio)) {
					$id = $envio;
				} else {
					$pr = Producto::create([
						'origen' => $code,
						'nombre' => 'ENVIO FLASH',
						'aduana' => 'envio flash',
						'codigo_barra' => $code,
						'imagen' => '/images/productos/sin_foto.png',
						'stock' => 999999999,
						'stock_minimo' => 9999
					]);
					$id = $pr;
				}
				return $id;

				break;
			case 'Entrega Pick Up Interior - Xpres':
				$code = 103;

				$envio = Producto::where('origen', $code)->first();

				if (!is_null($envio)) {
					$id = $envio;
				} else {
					$pr = Producto::create([
						'origen' => $code,
						'nombre' => 'ENTREGA PICK UP INTERIOR - XPRES',
						'aduana' => 'ENTREGA PICK UP INTERIOR - XPRES',
						'codigo_barra' => $code,
						'imagen' => '/images/productos/sin_foto.png',
						'stock' => 999999999,
						'stock_minimo' => 9999
					]);
					$id = $pr;
				}
				return $id;

				break;

			default:
				# code...
				break;
		}
	}
}
