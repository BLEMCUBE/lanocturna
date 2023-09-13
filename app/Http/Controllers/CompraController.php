<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompraStoreRequest;
use App\Http\Requests\CompraUpdateRequest;
use App\Http\Resources\ProductoVentaCollection;
use App\Http\Resources\CompraCollection;
use Exception;
use App\Models\Producto;
use App\Models\Compra;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\CompraResource;

use Illuminate\Support\Facades\Request;


class CompraController extends Controller
{
    public function __construct()
    {
        //protegiendo el controlador segun el rol
        //$this->middleware(['auth', 'permission:lista-compras'])->only('index');
        //$this->middleware(['auth', 'permission:crear-compras'])->only(['store','create']);
        //$this->middleware(['auth', 'permission:editar-compras'])->only(['update']);
        //$this->middleware(['auth', 'permission:eliminar-compras'])->only(['destroy']);
    }

    public function index()
    {



        $venta_query = Compra::select('*')->when(Request::input('inicio'), function ($query, $search) {
            $query->whereDate('created_at', '>=', $search);
        })
            ->when(Request::input('fin'), function ($query, $search) {
                $query->whereDate('created_at', '<=', $search);
            })->orderBy('created_at', 'DESC')
            ->get();
        return Inertia::render('Compra/Index', [
            'ventas' => new CompraCollection(
                $venta_query
            )
        ]);
    }
    public function create()
    {


        return Inertia::render('Compra/Create', [
            'productos' => new ProductoVentaCollection(
                Producto::orderBy('created_at', 'DESC')
                    ->get()
            )
        ]);
    }
    public function edit($id)
    {
        $compra = Compra::with(['detalles_compras' => function ($query) {
            $query->select('*')->with(['producto' => function ($query) {
                $query->select('id', 'nombre', 'codigo_barra', 'origen');
            }]);
        }])

            ->with(['facturador' => function ($query) {
                $query->select('id', 'name');
            }])->select('*')
            ->orderBy('id', 'DESC')->findOrFail($id);
        return Inertia::render('Compra/Edit', [
            'compra' => $compra,
            'productos' => new ProductoVentaCollection(
                Producto::orderBy('created_at', 'DESC')
                    ->get()
            )
        ]);
    }

    public function store(CompraStoreRequest $request)
    {

        $usuario = auth()->user();
        DB::beginTransaction();
        try {

            //creando compra
            $compra = Compra::create([
                'nro_factura' => $request->nro_factura ?? '',
                'proveedor' => $request->proveedor,
                'observaciones' => $request->observaciones,
                'facturador_id' => $usuario->id,

            ]);
            //actualizando stock producto
            foreach ($compra->detalles_compras as $produc) {
                $prod = Producto::find($produc['producto_id']);
                $old_stock = $prod->stock;

                $new_stock = $old_stock + $produc['cantidad'];
                $prod->update([
                    "stock" => $new_stock,
                    "stock_futuro"=>$new_stock+$prod->en_camino
                ]);
            }
            //creando detalle venta
            foreach ($request->productos as $producto) {

                $compra->detalles_compras()->create(
                    [
                        "producto_id" => $producto['producto_id'],
                        "cantidad" => $producto['cantidad'],

                    ]
                );
            }

            //actualizando stock producto
            foreach ($compra->detalles_compras as $produc) {
                $prod = Producto::find($produc['producto_id']);
                $old_stock = $prod->stock;

                $new_stock = $old_stock - $produc['cantidad'];
                $prod->update([
                    "stock" => $new_stock,
                    "stock_futuro"=>$new_stock+$prod->en_camino
                ]);
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
    public function update(CompraUpdateRequest $request, $id)
    {
        $venta = Compra::find($id);

        DB::beginTransaction();
        try {
            $venta->nro_factura = $request->nro_factura;
            $venta->proveedor = $request->proveedor;
            $venta->observaciones = $request->observaciones;
            $venta->save();
            //actualizando stock producto
             foreach ($venta->detalles_compras as $producto) {
                $prod = Producto::find($producto['producto_id']);
                $old_stock = $prod->stock;
                $new_stock = $old_stock + $producto['cantidad'];
                $prod->update([
                    "stock" => $new_stock,
                    "stock_futuro"=>$new_stock+$prod->en_camino
                ]);
            }
            //eliminando  detalle
            $venta->detalles_compras()->delete();

            //creando detalle compra
            foreach ($request->productos as $producto) {

                $venta->detalles_compras()->create(
                    [
                        "producto_id" => $producto['producto_id'],
                        "cantidad" => $producto['cantidad'],
                    ]
                );
            }

            //actualizando stock producto
            foreach ($request->productos  as $proo2) {
                $prod = Producto::find($proo2['producto_id']);
                $old_stock = $prod->stock;
                $new_stock = $old_stock - $proo2['cantidad'];
                $prod->update([
                    "stock" => $new_stock,
                    "stock_futuro"=>$new_stock+$prod->en_camino
                ]);
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
        $venta_query = Compra::with(['detalles_compras' => function ($query) {
            $query->select('compra_detalles.*')->with(['producto' => function ($query) {
                $query->select('id', 'nombre', 'codigo_barra', 'origen');
            }]);
        }])
            ->with(['facturador' => function ($query) {
                $query->select('id', 'name');
            }])->select('*')->select('*')->orderBy('id', 'DESC')->findOrFail($id);
        $compra = new CompraResource($venta_query);

        return Inertia::render('Compra/Show', [
            'compra' => $compra
        ]);
    }

    public function destroy($id)
    {
        $venta = Compra::find($id);

        DB::beginTransaction();
        try {
            $venta->estado = "ANULADO";
            $venta->fecha_anulacion =  now();
            $venta->save();

                //actualizando stock producto
                foreach ($venta->detalles_compras as $producto) {
                    $prod = Producto::find($producto['producto_id']);
                    $old_stock = $prod->stock;
                    $new_stock = $old_stock + $producto['cantidad'];
                    $prod->update([
                        "stock" => $new_stock,
                        "stock_futuro"=>$new_stock+$prod->en_camino
                    ]);
                }

            //eliminando  detalle
            $venta->detalles_compras()->delete();

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
