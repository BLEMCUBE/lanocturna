<?php

namespace App\Services;

use App\Models\Producto;
use Illuminate\Support\Facades\Hash;
use App\Models\TipoCambio;
use Exception;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VentaService
{
	public function __construct(
		private ProductoService $productoService
	) {}

	public function crearEnvio(Request $request)
	{

		$usu = 'enviostienda';

		$buscarUsuario = User::where('username', '=', $usu)->first();
		$usu_id = null;

		if ($buscarUsuario == null) {
			$user = new User();
			$user->name     = 'Envios Tienda';
			$user->username     = $usu;
			$user->password = Hash::make('123456');
			$user->photo = "/images/usuarios/user.png";
			$user->save();
			$user->assignRole('Vendedor');
			$usu_id = $user->id;
		} else {
			$usu_id = $buscarUsuario->id;
		}

		$tipo_envio = $request->envio['id'];
		$last_cambio = TipoCambio::orderBy('id', 'desc')->first();
		$productos = [];

		$nro_orden =Venta::where(function ($query) use($request) {
				$query->where('nro_orden', $request->id);
			})
			/*->where(function ($query) {
				$query->whereNot('estado', "COMPLETADO")
					->orWhereNot('estado', "ANULADO");

			})*/
			->first();

	//dd($nro_orden);
		if (is_null($nro_orden)) {

		$destino = '';

		switch ($tipo_envio) {
			case 'envio_flash':
				$destino = 'ENVIO FLASH';
				break;

			default:
				# code...
				break;
		}


		$cliente = [
			'nombre' => $request->cliente['envio']['nombre'] ?? '',
			'direccion' => $request->cliente['envio']['direccion'] ?? '',
			'telefono' => $request->cliente['envio']['telefono'] ?? '',
			'empresa' => $request->cliente['envio']['empresa'] ?? null,
			'rut' => $request->cliente['envio']['rut'] ?? null,
			'localidad' => $request->cliente['envio']['rut'] ?? null,

		];

		foreach ($request->productos as $producto) {
			$pr = $this->productoService->ProductoBySku($producto['sku']);

			if (!is_null($pr)) {

				array_push($productos, [
					"producto_id" => $pr['id'],
					"precio" => $producto['precio'],
					"precio_sin_iva" => round($producto['precio_sin_iva']),
					"cantidad" => $producto['cantidad'],
					"total" => $producto['total'],
					"total_sin_iva" => $producto['total_sin_iva'],
				]);
			}
		}

		//envio
		$envio=$this->productoService->ProductoEnvio($tipo_envio);
		if(!is_null($envio)){


		array_push($productos, [
					"producto_id" => $envio['id'],
					"precio" => $request->envio['monto'],
					"precio_sin_iva" => round( $request->envio['monto_sin_iva']),
					"cantidad" => 1,
					"total"  =>$request->envio['monto'],
					"total_sin_iva" => $request->envio['monto_sin_iva'],
				]);

	}
		DB::beginTransaction();
		try {

			//creando venta
			$venta = Venta::create([
				'estado' => 'PENDIENTE DE FACTURACIÓN',
				'moneda' => 'Pesos',
				'destino' => $destino,
				'nro_orden' => $request->id,
				'nro_compra' => $request->id,
				'tipo_cambio' => $last_cambio->valor,
				'observaciones' => $request->observacion,
				'cliente' => json_encode($cliente),
				//'parametro' => json_encode($request->all()),
				'vendedor_id' => $usu_id,
				'total_sin_iva' => $request->total_sin_iva ?? 0,
				'total' => $request->total ?? 0,

			]);
			$venta->update([
				"codigo" => zero_fill($venta->id, 8)
			]);

			//creando detalle venta
			foreach ($productos as $producto) {

				$venta->detalles_ventas()->create(
					[
						"producto_id" => $producto['producto_id'],
						"precio" => $producto['precio'],
						"precio_sin_iva" => round($producto['precio_sin_iva']),
						"cantidad" => $producto['cantidad'],
						"total" => $producto['total'],
						"total_sin_iva" => $producto['total_sin_iva'],
					]
				);
			}

			DB::commit();
			return [
				'nro_orden' => $request->id,
				'estado' => true,
				'mensaje' => "Venta Creada",
				'datos' => []
			];
		} catch (Exception $e) {
			DB::rollBack();
			return [
				//'message' => $e->getMessage(),
				'nro_orden' => '',
				'estado' => false,
				'mensaje' => "Nro de orden ya existe",
				'datos' => [],
			];
		}

			} else {
			return [
				'nro_orden' => $request->id,
				'estado' => false,
				'mensaje' => "Nro de orden ya existe",
				'datos' => [],
			];
		}


	}
}
