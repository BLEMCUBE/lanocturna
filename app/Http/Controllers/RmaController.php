<?php

namespace App\Http\Controllers;

use App\Http\Requests\EnvioUpdateRequest;
use App\Http\Requests\RmaStoreRequest;
use App\Http\Requests\VentaStoreRequest;
use App\Http\Requests\VentaUpdateRequest;
use App\Http\Resources\ProductoRmaCollection;
use App\Http\Resources\ProductoVentaCollection;
use App\Http\Resources\VentaCollection;
use App\Models\Cliente;
use Exception;
use App\Models\Producto;
use Carbon\Carbon;
use App\Models\Venta;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\VentaResource;
use App\Models\Destino;
use App\Models\Rma;
use App\Models\TipoCambio;
use Illuminate\Support\Facades\Request;


class RmaController extends Controller
{
    public function __construct()
    {
        //protegiendo el controlador segun el rol
        //$this->middleware(['auth', 'permission:lista-rma'])->only('index');
        //$this->middleware(['auth', 'permission:crear-rma'])->only(['store','create']);
        //$this->middleware(['auth', 'permission:editar-rma'])->only(['update']);
        //$this->middleware(['auth', 'permission:eliminar-rma'])->only(['destroy']);
    }

    public function index()
    {

        $ultimo_tipo_cambio = TipoCambio::all()->last();

        $hoy_tipo_cambio = false;

        $actual = Carbon::now()->format('Y-m-d');
        if (!empty($ultimo_tipo_cambio)) {
            $fecha = Carbon::create($ultimo_tipo_cambio->created_at->format('Y-m-d'));
            if ($fecha->eq($actual)) {
                $hoy_tipo_cambio = true;
                $tipo_cambio = $ultimo_tipo_cambio;
            } else {
                $hoy_tipo_cambio = false;
            }
        }

        $venta_query = Venta::select('*')->when(Request::input('inicio'), function ($query, $search) {
            $query->whereDate('created_at', '>=', $search);
        })
            ->when(Request::input('fin'), function ($query, $search) {
                $query->whereDate('created_at', '<=', $search);
            })->orderBy('created_at', 'DESC')
            ->get();
        return Inertia::render('Rma/Index', [
            'tipo_cambio' => $hoy_tipo_cambio,
            'ventas' => new VentaCollection(
                $venta_query
            )
        ]);
    }
    public function rma_create()
    {
        $hoy_tipo_cambio = false;

        $actual = Carbon::now()->format('Y-m-d');


        $vendedor = auth()->user();
        /*$last = Rma::latest()->first();

        if (empty($last) || is_null($last)) {
            $nro_servicio = zero_fill(1, 5);
        } else {
            $nro_servicio = zero_fill($last->nro_servicio + 1, 5);
        }*/
        $last = Rma::latest()->first();

        if (empty($last) || is_null($last)) {
            $nro_servicio = zero_fill(1, 5);
        } else {
            $nro_servicio = zero_fill($last->id + 1, 5);
        }


        return Inertia::render('Rma/RmaCreate', [
            'nro_servicio' => $nro_servicio,
            'user_id' => $vendedor->id,
            'vendedor' => $vendedor->name,
            'productos' => new ProductoRmaCollection(
                Producto::orderBy('created_at', 'DESC')
                    ->get()
            )
        ]);
    }

    public function edit($id)
    {
        //Lista cliente
        $lista_clientes = Cliente::get();

        $clientes = [];
        foreach ($lista_clientes as $cliente) {
            array_push($clientes, [
                'value' => $cliente->id,
                'label' => $cliente->nombre,
            ]);
        }
        //Lista destino
        $lista_destin = Destino::get();

        $lista_destinos = [];
        foreach ($lista_destin as $destino) {
            array_push($lista_destinos, [
                'value' => $destino->nombre,
                'label' =>  $destino->nombre,
            ]);
        }


        $venta = Venta::with(['detalles_ventas' => function ($query) {
            $query->select('*')->with(['producto' => function ($query) {
                $query->select('id', 'nombre', 'codigo_barra', 'origen');
            }]);
        }])
            ->with(['vendedor' => function ($query) {
                $query->select('users.id', 'users.name');
            }])
            ->with(['facturador' => function ($query) {
                $query->select('id', 'name');
            }])
            ->with(['validador' => function ($query) {
                $query->select('id', 'name');
            }])
            ->orderBy('id', 'DESC')->findOrFail($id);
        //return $venta;
        if ($venta->tipo == "VENTA") {
            return Inertia::render('Venta/Edit', [
                'lista_destinos' => $lista_destinos,
                'venta' => $venta,
                'productos' => new ProductoVentaCollection(
                    Producto::orderBy('created_at', 'DESC')
                        ->get()
                )
            ]);
        } else {

            return Inertia::render('Venta/EditMercado', [
                'lista_destinos' => $lista_destinos,
                'venta' => $venta,
                'productos' => new ProductoVentaCollection(
                    Producto::orderBy('created_at', 'DESC')
                        ->get()
                )
            ]);
        }
    }

    public function rma_store(RmaStoreRequest $request)
    {
        $vendedor = auth()->user();

        DB::beginTransaction();
        try {

            //creando rma
            $rma = Rma::create([
                'fecha_ingreso' => $request->fecha_ingreso,
                'fecha_compra' => $request->fecha_compra??null,
                'nro_factura' => $request->nro_factura??'',
                'cliente' => json_encode($request->cliente),
                'estado' => $request->estado,
                'tipo' => $request->tipo,
                'costo_presupuestado' => $request->costo_presupuestado ?? 0,
                'producto_id' => $request->producto_id??null,
                'prod_cantidad' => $request->prod_cantidad ?? 1,
                'prod_origen' => $request->prod_origen??'',
                'prod_serie' => $request->prod_serie??'',
                'prod_nombre' => $request->prod_nombre??'',
                'observaciones' => $request->observaciones,
                'defecto' => $request->defecto,
                'vendedor_id' => $vendedor->id,

            ]);
            $rma->update([
                "nro_servicio" => zero_fill($rma->id, 5)
            ]);




            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
    public function update(VentaUpdateRequest $request, $id)
    {
        $venta = Venta::find($id);

        DB::beginTransaction();
        try {
            $venta->codigo = $request->codigo;
            $venta->total_sin_iva =  $request->total_sin_iva ?? 0;
            $venta->total =  $request->total ?? 0;
            $venta->moneda = $request->moneda;
            $venta->tipo_cambio = $request->tipo_cambio;
            $venta->destino = $request->destino;
            $venta->cliente = json_encode($request->cliente);
            $venta->observaciones = $request->observaciones;
            $venta->vendedor_id = $request->vendedor_id;
            $venta->save();

            if ($venta->old_estado != 'PENDIENTE DE FACTURACIÓN') {

                //actualizando stock producto
                foreach ($venta->detalles_ventas as $producto) {
                    $prod = Producto::find($producto['producto_id']);
                    $old_stock = $prod->stock;
                    $new_stock = $old_stock + $producto['cantidad'];
                    $prod->update([
                        "stock" => $new_stock,
                        "stock_futuro"=>$new_stock+$prod->en_camino
                    ]);
                }
            }
            //eliminando  detalle
            $venta->detalles_ventas()->delete();

            //creando detalle venta
            foreach ($request->productos as $producto) {

                $venta->detalles_ventas()->create(
                    [
                        "producto_id" => $producto['producto_id'],
                        "precio" => $producto['precio'],
                        "precio_sin_iva" => $producto['precio_sin_iva'],
                        "cantidad" => $producto['cantidad'],
                        "total" => $producto['total'],
                        "total_sin_iva" => $producto['total_sin_iva'],
                    ]
                );
            }

            if ($venta->old_estado != 'PENDIENTE DE FACTURACIÓN') {

                //actualizando stock producto
                foreach ($request->productos  as $proo) {
                    $prod = Producto::find($proo['producto_id']);
                    $old_stock = $prod->stock;
                    $new_stock = $old_stock - $proo['cantidad'];
                    $prod->update([
                        "stock" => $new_stock,
                        "stock_futuro"=>$new_stock+$prod->en_camino
                    ]);
                }
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
    public function updatemercado(EnvioUpdateRequest $request, $id)
    {
        $venta = Venta::find($id);

        DB::beginTransaction();
        try {
            $venta->codigo = $request->codigo;
            $venta->total_sin_iva =  0;
            $venta->total =  0;
            $venta->moneda = $request->moneda;
            $venta->tipo_cambio = $request->tipo_cambio;
            $venta->destino = $request->destino;
            $venta->nro_compra=$request->nro_compra;
            $venta->cliente = json_encode($request->cliente);
            $venta->observaciones = $request->observaciones;
            $venta->vendedor_id = $request->vendedor_id;
            $venta->save();

            if ($venta->old_estado != 'PENDIENTE DE FACTURACIÓN') {

                //actualizando stock producto
                foreach ($venta->detalles_ventas as $producto) {
                    $prod = Producto::find($producto['producto_id']);
                    $old_stock = $prod->stock;
                    $new_stock = $old_stock + $producto['cantidad'];
                    $prod->update([
                        "stock" => $new_stock,
                        "stock_futuro"=>$new_stock+$prod->en_camino
                    ]);
                }
            }
            //eliminando  detalle
            $venta->detalles_ventas()->delete();

            //creando detalle venta
            foreach ($request->productos as $producto) {

                $venta->detalles_ventas()->create(
                    [
                        "producto_id" => $producto['producto_id'],
                        "precio" => 0,
                        "precio_sin_iva" => 0,
                        "cantidad" => $producto['cantidad'],
                        "total" => 0,
                        "total_sin_iva" => 0,
                    ]
                );
            }

            if ($venta->old_estado != 'PENDIENTE DE FACTURACIÓN') {

                //actualizando stock producto
                foreach ($request->productos  as $proo) {
                    $prod = Producto::find($proo['producto_id']);
                    $old_stock = $prod->stock;
                    $new_stock = $old_stock - $proo['cantidad'];
                    $prod->update([
                        "stock" => $new_stock,
                        "stock_futuro"=>$new_stock+$prod->en_camino
                    ]);
                }
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
    public function show($id)
    {
        $venta_query = Venta::with(['detalles_ventas' => function ($query) {
            $query->select('venta_detalles.*')->with(['producto' => function ($query) {
                $query->select('id', 'nombre', 'codigo_barra', 'origen');
            }]);
        }])
            ->with(['vendedor' => function ($query) {
                $query->select('users.id', 'users.name');
            }])
            ->with(['facturador' => function ($query) {
                $query->select('id', 'name');
            }])
            ->with(['validador' => function ($query) {
                $query->select('id', 'name');
            }])
            ->orderBy('id', 'DESC')->findOrFail($id);
        //return $venta_query;
        $venta = new VentaResource($venta_query);
        return Inertia::render('Venta/Show', [
            'venta' => $venta
        ]);
    }

    public function destroy($id)
    {
        $venta = Venta::find($id);
        $old_estado = $venta->estado;

        DB::beginTransaction();
        try {
            $venta->estado = "ANULADO";
            $venta->facturado = 0;
            $venta->fecha_anulacion =  now();
            $venta->save();



            if ($old_estado != 'PENDIENTE DE FACTURACIÓN') {

                //actualizando stock producto
                foreach ($venta->detalles_ventas as $producto) {
                    $prod = Producto::find($producto['producto_id']);
                    $old_stock = $prod->stock;
                    $new_stock = $old_stock + $producto['cantidad'];
                    $prod->update([
                        "stock" => $new_stock,
                        "stock_futuro"=>$new_stock+$prod->en_camino
                    ]);
                }
            }
            //eliminando  detalle
            $venta->detalles_ventas()->delete();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}
