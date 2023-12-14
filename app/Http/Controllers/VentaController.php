<?php

namespace App\Http\Controllers;

use App\Http\Requests\EnvioUpdateRequest;
use App\Http\Requests\VentaStoreRequest;
use App\Http\Requests\VentaUpdateRequest;
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
use App\Models\TipoCambio;
use Illuminate\Support\Facades\Request;


class VentaController extends Controller
{
    public function __construct()
    {
        //protegiendo el controlador segun el rol
        //$this->middleware(['auth', 'permission:lista-ventas'])->only('index');
        //$this->middleware(['auth', 'permission:crear-ventas'])->only(['store','create']);
        //$this->middleware(['auth', 'permission:editar-ventas'])->only(['update']);
        //$this->middleware(['auth', 'permission:eliminar-ventas'])->only(['destroy']);
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

        $venta_query = Venta::select('*')
        ->where(function ($query) {
            $query->where("tipo", "=", "VENTA")
                ->orWhere("tipo", "=", "ENVIO");
        })
            ->when(Request::input('inicio'), function ($query) {
                $query->whereDate('created_at', '>=', Request::input('inicio') . ' 00:00:00');
            })
            ->when(Request::input('fin'), function ($query) {
                $query->whereDate('created_at', '<=', Request::input('fin') . ' 23:59:00');
            })
            ->orderBy('created_at', 'DESC')
            ->get();
        return Inertia::render('Venta/Index', [
            'tipo_cambio' => $hoy_tipo_cambio,
            'ventas' => new VentaCollection(
                $venta_query
            )
        ]);
    }
    public function create()
    {
        $ultimo_tipo_cambio = TipoCambio::all()->last();

        $hoy_tipo_cambio = false;

        $actual = Carbon::now()->format('Y-m-d');
        if (!empty($ultimo_tipo_cambio)) {
            $fecha = Carbon::create($ultimo_tipo_cambio->created_at->format('Y-m-d'));
            if ($fecha->eq($actual)) {
                $hoy_tipo_cambio = true;
                $tipo_cambio = number_format($ultimo_tipo_cambio->valor, 2) ?? '';
            } else {
                $hoy_tipo_cambio = false;
            }
        }


        $last = Venta::latest()->first();
        $vendedor = auth()->user();

        if (empty($last) || is_null($last)) {
            $codigo = zero_fill(1, 8);
        } else {
            $codigo = zero_fill($last->codigo + 1, 8);
        }

        //Lista cliente
        $lista_clientes = Cliente::get();
        $lista_cliente = Cliente::select('id', 'nombre')->get();

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


$productoLista = Producto::with(['importacion_detalles' => function ($query) {
    $query->select('id','sku', 'cantidad_total', 'importacion_id','estado');
}, 'importacion_detalles.importacion' => function ($query1) {
    $query1->select('id', 'estado','nro_carpeta');
}])->select('*')
->orderBy('nombre', 'ASC')

->get();


$resultadoProductoLista=new ProductoVentaCollection($productoLista);

        return Inertia::render('Venta/Create', [
            'hoy_tipo_cambio' => $hoy_tipo_cambio,
            'tipo_cambio' => $tipo_cambio,
            'codigo' => $codigo,
            'user_id' => $vendedor->id,
            'vendedor' => $vendedor->name,
            'clientes' => $clientes,
            'lista_clientes' => $lista_cliente,
            'lista_destinos' => $lista_destinos,
            'productos' => $resultadoProductoLista
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
        $productoLista = Producto::with(['importacion_detalles' => function ($query) {
            $query->select('id','sku', 'cantidad_total', 'importacion_id','estado');
        }, 'importacion_detalles.importacion' => function ($query1) {
            $query1->select('id', 'estado','nro_carpeta');
        }])->select('*')
        ->orderBy('nombre', 'ASC')

        ->get();
        $resultadoProductoLista=new ProductoVentaCollection($productoLista);

        if ($venta->tipo == "VENTA") {
            return Inertia::render('Venta/Edit', [
                'lista_destinos' => $lista_destinos,
                'venta' => $venta,
                'productos' => $resultadoProductoLista
            ]);
        } else {

            return Inertia::render('Venta/EditMercado', [
                'lista_destinos' => $lista_destinos,
                'venta' => $venta,
                'productos' => $resultadoProductoLista
            ]);
        }
    }

    public function store(VentaStoreRequest $request)
    {
        $vendedor = auth()->user();

        DB::beginTransaction();
        try {

            //creando venta
            $venta = Venta::create([
                'total_sin_iva' => $request->total_sin_iva ?? 0,
                'total' => $request->total ?? 0,
                'estado' => $request->estado,
                'moneda' => $request->moneda,
                'tipo_cambio' => $request->tipo_cambio,
                'destino' => $request->destino,
                'cliente' => json_encode($request->cliente),
                'observaciones' => $request->observaciones,
                'vendedor_id' => $vendedor->id,

            ]);
            $venta->update([
                "codigo" => zero_fill($venta->id, 8)
            ]);
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
                        "stock_futuro" => $new_stock + $prod->en_camino
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
                        "stock_futuro" => $new_stock + $prod->en_camino
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
            $venta->nro_compra = $request->nro_compra;
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
                        "stock_futuro" => $new_stock + $prod->en_camino
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
                        "stock_futuro" => $new_stock + $prod->en_camino
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
                        "stock_futuro" => $new_stock + $prod->en_camino
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
