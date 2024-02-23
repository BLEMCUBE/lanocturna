<?php

namespace App\Http\Controllers;

use App\Http\Requests\RmaStoreRequest;
use App\Http\Requests\SubirRmaStoreRequest;
use App\Http\Resources\ProductoRmaCollection;
use App\Http\Resources\RmaCollection;
use App\Http\Resources\RmaResource;
use App\Http\Resources\VentaCollection;
use App\Http\Resources\VentaResource;
use App\Models\Configuracion;
use App\Models\Destino;
use Exception;
use App\Models\Producto;
use Carbon\Carbon;
use App\Models\Venta;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use App\Models\Rma;
use App\Models\RmaStock;
use Illuminate\Support\Facades\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request as Req;

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
        $venta_query = Rma::select('*')->when(Request::input('inicio'), function ($query, $search) {
            $query->whereDate('fecha_ingreso', '>=', $search);
        })
            ->when(Request::input('fin'), function ($query, $search) {
                $query->whereDate('fecha_ingreso', '<=', $search);
            })
            ->whereNot('modo', 'ENTREGADO')
            ->orderBy('nro_servicio', 'DESC')
            ->get();
        return Inertia::render('Rma/Index', [
            'ventas' => new RmaCollection(
                $venta_query
            )
        ]);
    }
    public function historial()
    {
        $venta_query = Rma::select('*')->when(Request::input('inicio'), function ($query, $search) {
            $query->whereDate('fecha_ingreso', '>=', $search);
        })
            ->when(Request::input('fin'), function ($query, $search) {
                $query->whereDate('fecha_ingreso', '<=', $search);
            })
            ->orderBy('nro_servicio', 'DESC')
            ->get();
        return Inertia::render('Rma/Historial', [
            'ventas' => new RmaCollection(
                $venta_query
            )
        ]);
    }


    public function rma_create()
    {
        $vendedor = auth()->user();

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

    public function rma_edit($id)
    {

        $venta_query = Rma::with(['vendedor' => function ($query) {
            $query->select('users.id', 'users.name');
        }])->with(['producto' => function ($query) {
            $query->select('productos.id', 'productos.imagen');
        }])
            ->orderBy('id', 'DESC')->findOrFail($id);
        $venta = new RmaResource($venta_query);

        return Inertia::render('Rma/RmaEdit', [
            'venta' => $venta,
            'productos' => new ProductoRmaCollection(
                Producto::orderBy('created_at', 'DESC')
                    ->get()
            )
        ]);
    }

    public function rma_store(RmaStoreRequest $request)

    {
        $vendedor = auth()->user();

        DB::beginTransaction();
        try {

            //creando rma
            $fecha_limite = Carbon::create($request->fecha_ingreso)->addDays(15)->format('Y-m-d');
            $rma = Rma::create([
                'fecha_ingreso' => $request->fecha_ingreso,
                'fecha_compra' => $request->fecha_compra ?? null,
                'fecha_limite' => $fecha_limite ?? null,
                'nro_factura' => $request->nro_factura ?? '',
                'cliente' => json_encode($request->cliente),
                'estado' => $request->estado,
                'modo' => $request->modo,
                'tipo' => $request->tipo,
                'costo_presupuestado' => $request->costo_presupuestado ?? 0,
                'producto_id' => $request->producto_id ?? null,
                'prod_cantidad' => $request->prod_cantidad ?? 1,
                'prod_origen' => $request->prod_origen ?? '',
                'prod_serie' => $request->prod_serie ?? '',
                'prod_nombre' => $request->prod_nombre ?? '',
                'observaciones' => $request->observaciones??'',
                'defecto' => $request->defecto??'',
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
    public function rma_update(RmaStoreRequest $request, $id)
    {
        $rma = Rma::find($id);


        DB::beginTransaction();
        try {
            $rma->update([
                'fecha_compra' => $request->fecha_compra ?? null,
                'nro_factura' => $request->nro_factura ?? '',
                'cliente' => json_encode($request->cliente),
                'estado' => $request->estado,
                'modo' => $request->modo,
                'tipo' => $request->tipo,
                'costo_presupuestado' => $request->costo_presupuestado ?? 0,
                'producto_id' => $request->producto_id ?? null,
                'prod_cantidad' => $request->prod_cantidad ?? 1,
                'prod_origen' => $request->prod_origen ?? '',
                'prod_serie' => $request->prod_serie ?? '',
                'prod_nombre' => $request->prod_nombre ?? '',
                'observaciones' => $request->observaciones??'',
                'defecto' => $request->defecto??'',
                'vendedor_id' => $request->vendedor_id,
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


    public function rma_subir()
    {
        $lista_rma = Rma::select('id', 'estado', 'nro_servicio')
            ->where('estado', '=', 'CAMBIO PRODUCTO')
            ->orWhere('estado', '=', 'REPARADO')
            ->get();

        $rmas = [];
        foreach ($lista_rma as $rm) {
            array_push($rmas, [
                'code' => $rm->id,
                'name' => $rm->nro_servicio,
            ]);
        }

        $last = Venta::latest()->first();
        $vendedor = auth()->user();

        if (empty($last) || is_null($last)) {
            $codigo = zero_fill(1, 8);
        } else {
            $codigo = zero_fill($last->codigo + 1, 8);
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

        return Inertia::render('Rma/RmaSubir', [
            'codigo' => $codigo,
            'vendedor_id' => $vendedor->id,
            'vendedor' => $vendedor->name,
            'lista_destinos' => $lista_destinos,
            'lista_rmas' => $rmas,
        ]);
    }

    public function show($id)
    {
        $venta_query = Rma::with(['vendedor' => function ($query) {
            $query->select('users.id', 'users.name');
        }])
            ->orderBy('id', 'DESC')->findOrFail($id);


        $rma = new RmaResource($venta_query);
        $ventas = Venta::all();
        foreach ($ventas as $key => $vent) {

            if (!is_null($vent->parametro)) {
                $json_text = json_decode($vent->parametro);

                if ($json_text->rma->nro_servicio == $rma->nro_servicio) {

                    //actualizando stock producto
                    foreach ($vent->detalles_ventas as $producto) {
                        if ($json_text->opt->mueve_stock == 'SI') {
                            $editable = false;
                        } else {
                            $editable = true;
                        }
                    }
                }
            } else {
                $editable = true;
            }
        }
        return Inertia::render('Rma/Show', [
            'venta' => $rma,
            'editable' => $editable
        ]);
    }

    public function showsubir($id)
    {
        $vendedor = auth()->user();
        $venta_query = Rma::with(['vendedor' => function ($query) {
            $query->select('users.id', 'users.name');
        }])->with(['producto' => function ($query) {
            $query->select('productos.id', 'productos.imagen', 'productos.stock');
        }])->orderBy('id', 'DESC')->findOrFail($id);
        $venta = new RmaResource($venta_query);
        return response()->json([
            'venta' => $venta,
            'vendedor' => $vendedor->name,
            'vendedor_id' => $vendedor->id,
        ]);
    }
    public function showHistorial($id)
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
        $venta = new VentaResource($venta_query);
        return Inertia::render('Rma/ShowHistorial', [
            'venta' => $venta
        ]);
    }


    public function subir_store(SubirRmaStoreRequest $request)
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
                'tipo_cambio' => $request->tipo_cambio ?? 'Pesos',
                'destino' => $request->destino,
                'tipo' => $request->tipo ?? 'ENVIO',
                'vendedor_id' => $vendedor->id,
                'cliente' => json_encode($request->cliente),
                'parametro' => json_encode($request->parametro),
                'observaciones' => $request->observaciones??'',

            ]);
            $venta->update([
                "codigo" => zero_fill($venta->id, 8)
            ]);

            //creando detalle venta
            foreach ($request->productos as $producto) {

                $venta->detalles_ventas()->create(
                    [
                        "producto_id" => $producto['producto_id'],
                        "cantidad" => $producto['cantidad'],

                    ]
                );
            }

            if ($request->parametro['opt']['mueve_stock'] == "SI") {
                //actualizando stock producto
                foreach ($request->productos as $produ) {
                    $prod = Producto::find($produ['producto_id']);
                    $old_stock = $prod->stock;
                    $new_stock = $old_stock - $produ['cantidad'];
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

    public function historialEnvios()
    {
        $venta_query = new VentaCollection(
            Venta::where(function ($query) {
                $query->where('destino', "CADETERIA")
                    ->orWhere('destino', "FLEX")
                    ->orWhere('destino', "UES")
                    ->orWhere('destino', "DAC");
            })->select('*')->when(Request::input('inicio'), function ($query, $search) {
                $query->whereDate('created_at', '>=', $search);
            })
                ->when(Request::input('fin'), function ($query, $search) {
                    $query->whereDate('created_at', '<=', $search);
                })
                ->where("tipo", '=', "RMA")
                ->where('estado', 'COMPLETADO')->orderBy('id', 'DESC')->get()
        );
        return Inertia::render('Rma/HistorialEnvio', [
            'ventas' => new VentaCollection(
                $venta_query
            )
        ]);
    }
    public function generarTicket($id)
    {
        $rma_query = Rma::with(['vendedor' => function ($query) {
            $query->select('users.id', 'users.name');
        }])->findOrFail($id);
        $rma = new RmaResource($rma_query);


        if (!empty($rma)) {
            $customPaper = array(0, 0, 226.77, 283.46);
            $rma_cliente = json_decode($rma->cliente);
            $data = [
                'nro_servicio' => $rma->nro_servicio,
                'cliente' => $rma_cliente->nombre ?? '',
                'producto' => $rma->prod_origen . " / " . $rma->prod_nombre ?? '',
                'defecto' => $rma->defecto ?? '',
                'observaciones' => $rma->observaciones ?? '',
                'fecha' => (now())->format('d/m/Y H:i:s')
            ];
            $pdf = Pdf::loadView('pdfs.ticketEnvioRma', ['data' => $data]);
            $pdf->setPaper($customPaper);
            return $pdf->stream('ticket_' . $data['nro_servicio'] . '.pdf');
        } else {
            return Redirect::route('rmas.index');
        }
    }

    public function rma_stock()
    {
        $query_depositos = RmaStock::with(['producto' => function ($query) {
            $query->select('id', 'origen', 'nombre', 'imagen', 'codigo_barra');
        }])->with(['rma' => function ($query) {
            $query->select('id', 'defecto', 'observaciones');
        }])->select('*')->orderBy('producto_completo', 'DESC')->get();

        $grouped = $query_depositos->groupBy('producto_completo');

        $depositos = [];
        $det_producto = [];
        $id_stock = 0;
        $prod_completo = "SI";

        foreach ($grouped as $deposito) {

            foreach ($deposito as $prod) {

                array_push($det_producto, [
                    "producto_id" => $prod->producto->id,
                    "sku" => $prod->producto->origen,
                    "cantidad_total" => $prod->cantidad_total,
                    "nombre" => $prod->producto->nombre,
                    "imagen" => $prod->producto->imagen,
                    "defecto" => $prod->rma->defecto??'',
                    "observaciones" => $prod->rma->observaciones??'',
                    'rma_id' => $prod->rma_id,
                    'stock_id' => $prod->id
                ]);
                $id_stock = $prod->id;
                $prod_completo = $prod->producto_completo;
            }

            array_push($depositos, [
                "id" => $id_stock,
                "nombre" =>  $prod_completo == "SI" ? "PRODUCTOS COMPLETO" : "PRODUCTOS PARCIALES",
                "productos" => $det_producto,

            ]);
            $det_producto = [];
        }

        return Inertia::render('Rma/StockRma', [
            'depositos' => $depositos
        ]);
    }

    public function destroyStock($id)
    {
        $rma = RmaStock::find($id);
        $rma->delete();
    }

    public function destroy($id)
    {
        $rma = Rma::find($id);

        $ventas = Venta::all();
        foreach ($ventas as $key => $venta) {

            if (!is_null($venta->parametro)) {
                $json_text = json_decode($venta->parametro);

                if ($json_text->rma->nro_servicio == $rma->nro_servicio) {

                    //actualizando stock producto
                    foreach ($venta->detalles_ventas as $producto) {
                        if ($json_text->opt->mueve_stock == 'SI') {
                            $prod = Producto::find($producto->producto_id);
                            $old_stock = $prod->stock;
                            $new_stock = $old_stock + $producto->cantidad;
                            $prod->update([
                                "stock" => $new_stock,
                                "stock_futuro" => $new_stock + $prod->en_camino
                            ]);
                        }
                    }
                    $venta->detalles_ventas()->delete();
                    $venta->delete();
                }
            }
        }

        $rma->delete();
    }


    public function validacionRma()
    {
        $venta_query = new VentaCollection(
            Venta::where(function ($query) {
                $query->where('destino', "CADETERIA")
                    ->orWhere('destino', "FLEX")
                    ->orWhere('destino', "UES")
                    ->orWhere('destino', "DAC")
                    ->orWhere('destino', "WEB")
                    ->orWhere('destino', "MERCADOLIBRE")
                    ->orWhere('destino', "SALON");
            })->select('*')->when(Request::input('inicio'), function ($query, $search) {
                $query->whereDate('created_at', '>=', $search);
            })
                ->when(Request::input('fin'), function ($query, $search) {
                    $query->whereDate('created_at', '<=', $search);
                })
                ->where("tipo", '=', "RMA")
                ->where("facturado", '=', "0")
                ->orderBy('created_at', 'DESC')->get()
        );
        return Inertia::render('Rma/Validacion', [
            'ventas' => new VentaCollection(
                $venta_query
            )
        ]);
    }

    public function validacionRmaShow($id)
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
        $venta = new VentaResource($venta_query);
        return Inertia::render('Rma/ValidacionShow', [
            'venta' => $venta
        ]);
    }
    public function verificarCodigoMaestro(Req $request)
    {
        $codigo = Configuracion::where('slug', 'codigo-maestro')->first();
        $validated = $request->validate([
            'codigo' => 'required',
        ]);

        if (Hash::check($request->codigo, $codigo->value)) {

            $venta = Venta::with('detalles_ventas')->orderBy('id', 'DESC')->findOrFail($request->index);
            $facturador = auth()->user();
            $parametro_rma = json_decode($venta->parametro);
            DB::beginTransaction();
            try {
                $venta->estado = "VALIDADO";
                $venta->facturado = true;
                $venta->facturador_id =  $facturador->id;
                $venta->fecha_facturacion = now();
                $venta->save();

                //actualizando stock producto
                if ($parametro_rma->opt->mueve_stock == 'SI') {
                    foreach ($venta->detalles_ventas as $producto) {
                        $prod = Producto::find($producto['producto_id']);
                        $old_stock = $prod->stock;
                        $new_stock = $old_stock - $producto['cantidad'];
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
        } else {
            throw ValidationException::withMessages([
                'codigo' => __('Código maestro inválido'),
            ]);
        }
    }
}
