<?php

namespace App\Http\Controllers;

use App\Http\Requests\EnvioUpdateRequest;
use App\Http\Requests\RmaStoreRequest;
use App\Http\Resources\ProductoRmaCollection;
use App\Http\Resources\RmaCollection;
use App\Http\Resources\RmaResource;
use Exception;
use App\Models\Producto;
use Carbon\Carbon;
use App\Models\Venta;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use App\Models\Rma;
use Illuminate\Support\Facades\Request;
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
            })->orderBy('fecha_ingreso', 'DESC')
            ->whereNot('modo','ENTREGADO')
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
            })->orderBy('fecha_ingreso', 'DESC')
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
    public function rma_update(RmaStoreRequest $request, $id)
    {
        $rma = Rma::find($id);


        DB::beginTransaction();
        try {
            $rma->update([
                //                'fecha_ingreso' => $request->fecha_ingreso,
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
                'observaciones' => $request->observaciones,
                'defecto' => $request->defecto,
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

      public function show($id)
    {
        $venta_query = Rma::with(['vendedor' => function ($query) {
            $query->select('users.id', 'users.name');
        }])
            ->orderBy('id', 'DESC')->findOrFail($id);
        $venta = new RmaResource($venta_query);
        return Inertia::render('Rma/Show', [
            'venta' => $venta
        ]);
    }

    public function destroy($id)
    {
        $rma = Rma::find($id);
        $rma->delete();
    }
}
