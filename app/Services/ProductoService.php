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

			case 'UES Domicilio - 48 Horas Interior':
				$code = 104;
				$envio = Producto::where('origen', $code)->first();

				if (!is_null($envio)) {
					$id = $envio;
				} else {
					$pr = Producto::create([
						'origen' => $code,
						'nombre' => 'UES DOMICILIO - 48 HORAS INTERIOR',
						'aduana' => 'UES DOMICILIO - 48 HORAS INTERIOR',
						'codigo_barra' => $code,
						'imagen' => '/images/productos/sin_foto.png',
						'stock' => 999999999,
						'stock_minimo' => 9999
					]);
					$id = $pr;
				}
				return $id;
				break;

			case 'RECOGIDA LOCAL':
				$code = 105;
				$envio = Producto::where('origen', $code)->first();

				if (!is_null($envio)) {
					$id = $envio;
				} else {
					$pr = Producto::create([
						'origen' => $code,
						'nombre' => 'RECOGIDA LOCAL',
						'aduana' => 'RECOGIDA LOCAL',
						'codigo_barra' => $code,
						'imagen' => '/images/productos/sin_foto.png',
						'stock' => 999999999,
						'stock_minimo' => 9999
					]);
					$id = $pr;
				}
				return $id;
				break;

			case 'ENVÍO GRATUITO':
				$code = 106;
				$envio = Producto::where('origen', $code)->first();

				if (!is_null($envio)) {
					$id = $envio;
				} else {
					$pr = Producto::create([
						'origen' => $code,
						'nombre' => 'ENVÍO GRATUITO',
						'aduana' => 'ENVÍO GRATUITO',
						'codigo_barra' => $code,
						'imagen' => '/images/productos/sin_foto.png',
						'stock' => 999999999,
						'stock_minimo' => 9999
					]);
					$id = $pr;
				}
				return $id;
				break;

			case 'Entrega en Pick Up':
				$code = 107;
				$envio = Producto::where('origen', $code)->first();

				if (!is_null($envio)) {
					$id = $envio;
				} else {
					$pr = Producto::create([
						'origen' => $code,
						'nombre' => 'ENTREGA EN PICK UP',
						'aduana' => 'ENTREGA EN PICK UP',
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
