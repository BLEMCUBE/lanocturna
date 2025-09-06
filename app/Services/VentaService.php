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
		$titulo_envio = $request->envio['titulo'];
		$zona_envio = $request->envio['zona'];
		$last_cambio = TipoCambio::orderBy('id', 'desc')->first();
		$productos = [];

		$nro_orden = Venta::where(function ($query) use ($request) {
			$query->where('nro_orden', $request->id);
		})->first();


		if (is_null($nro_orden)) {

			$destino = '';


			if ($zona_envio == 'Montevideo') {

				switch ($titulo_envio) {
					case 'Envío Flash':
						$destino = 'ENVIO FLASH';
						break;
					case 'RECOGIDA LOCAL':
						$destino = 'RETIROS WEB';
						break;
					case 'ENVÍO GRATUITO':
						$destino = 'CADETERIA';
						break;
					case 'UES Estandar - 24 Horas':
						$destino = 'CADETERIA';
						break;
				}
			} else {
				//interior
				switch ($titulo_envio) {
					case 'RECOGIDA LOCAL':
						$destino = 'RETIROS WEB';
						break;
					case 'Entrega Pick Up Interior - Xpres':
						$destino = 'UES WEB';
						break;
					case 'UES Domicilio - 48 Horas Interior':
						$destino = 'UES WEB';
						break;
				}
			}



			$cliente = [
				'nombre' => $request->cliente['envio']['nombre'] ?? '',
				'direccion' => $request->cliente['envio']['direccion'] ?? '',
				'nro_casa' => $request->cliente['envio']['nro_casa'] ?? '',
				'telefono' => $request->cliente['envio']['telefono'] ?? '',
				'empresa' => $request->cliente['envio']['empresa'] ?? null,
				'rut' => $request->cliente['envio']['rut'] ?? null,
				'localidad' => $request->cliente['envio']['localidad'] ?? null,

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
			$envio = $this->productoService->ProductoEnvio($titulo_envio);
			if (!is_null($envio)) {


				array_push($productos, [
					"producto_id" => $envio['id'],
					"precio" => $request->envio['monto'],
					"precio_sin_iva" => round($request->envio['monto_sin_iva']),
					"cantidad" => 1,
					"total"  => $request->envio['monto'],
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
					'mp_id' => $request->mp_id ?? '',
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
					'destino' => $destino,
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

	public function userId($userId = null, $f_inicio = null, $f_final = null): array
	{
		$query_data = DB::table('users as us')
			->join('ventas as ve', 'us.id', '=', 've.vendedor_id')
			->when($f_inicio, function ($query, $f_inicio) {
				$query->whereDate('ve.fecha_facturacion', '>=', $f_inicio);
			})
			->when($f_final, function ($query, $f_final) {
				$query->whereDate('ve.fecha_facturacion', '<=', $f_final);
			})
			->when($userId, function ($query, $userId) {
				$query->where('us.id', '=', $userId);
			})
			->select('us.id')
			->groupBy('us.id')
			->get()->toArray();
		return $query_data;
	}

	public function ventasByUser($userId = null, $f_inicio = null, $f_final = null, $facturado = 1, $tipo = 'VENTA')
	{
		/*
		DB::table('users as us')
						->join('ventas as ve', 'us.id', '=', 've.vendedor_id')
						->when($inicio, function ($query, $inicio) {
							$query->whereDate('ve.fecha_facturacion', '>=', $inicio);
						})
						->when($final, function ($query, $final) {
							$query->whereDate('ve.fecha_facturacion', '<=', $final);
						})
						->where('ve.facturado', '=', '1')
						->where('ve.tipo', '=', 'VENTA')
						->where('us.id', '=', $vend)
						->select(
							'us.name',
							'us.id',
							've.id',
							DB::raw("SUM(IF(ve.moneda = 'Pesos', ROUND(ve.total,2), ROUND(ve.total * ve.tipo_cambio,2))) AS ventas_totales"),
							DB::raw("COUNT(ve.id) AS pedidos"),
						)
						->groupBy('us.id')
						->orderBy('pedidos', 'ASC')
						->first()
						*/
		$query_data = DB::table('users as us')
			->join('ventas as ve', 'us.id', '=', 've.vendedor_id')
			->when($f_inicio, function ($query, $f_inicio) {
				$query->whereDate('ve.fecha_facturacion', '>=', $f_inicio);
			})
			->when($f_final, function ($query, $f_final) {
				$query->whereDate('ve.fecha_facturacion', '<=', $f_final);
			})

			->where('ve.facturado', '=', $facturado)
			->where('ve.tipo', '=', $tipo)
			->when($userId, function ($query, $userId) {
				$query->where('us.id', '=', $userId);
			})
			->select(
				'us.name',
				'us.id',
				've.id',
				DB::raw("SUM(IF(ve.moneda = 'Pesos', ROUND(ve.total,2), ROUND(ve.total * ve.tipo_cambio,2))) AS ventas_totales"),
				DB::raw("COUNT(ve.id) AS pedidos")
			)
			->groupBy('us.id')
			->orderBy('pedidos', 'ASC')
			->first();


		return $query_data;
	}

	public function ventasByUserWhereIn(array $userId = [], $f_inicio = null, $f_final = null, $facturado = 1)
	{
		$query_data = DB::table('users as us')
			->join('ventas as ve', 'us.id', '=', 've.vendedor_id')
			->when($f_inicio, function ($query, $f_inicio) {
				$query->whereDate('ve.fecha_facturacion', '>=', $f_inicio);
			})
			->when($f_final, function ($query, $f_final) {
				$query->whereDate('ve.fecha_facturacion', '<=', $f_final);
			})
			->select(
				'us.name',
				've.vendedor_id',
				've.id',
				DB::raw("IF(ve.moneda = 'Pesos', ve.total, ve.total*ve.tipo_cambio) as ventas_totales,COUNT(ve.id) AS pedidos"),
			)
			->when($userId, function ($query, $userId) {
				$query->whereIn('us.id',  $userId);
			})
			->where('ve.facturado', '=', $facturado)
			->groupBy('us.id')
			->orderBy('pedidos', 'ASC')
			->get();


		return $query_data;
	}
}
