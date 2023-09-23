<?php

namespace App\Http\Controllers;

use App\Exports\ImportacionesExport;
use App\Http\Requests\ImportacionStoreRequest;
use App\Http\Requests\ImportacionUpdateRequest;
use App\Http\Requests\ProductoImportacionUpdateRequest;
use App\Http\Resources\ImportacionCollection;
use App\Imports\ImportacionesImport;
use App\Models\Importacion;
use App\Models\ImportacionDetalle;
use App\Models\Producto;
use Inertia\Inertia;
use Exception;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\DB;

class ImportacionController extends Controller
{
    public function __construct()
    {
        //protegiendo el controlador segun el rol
        //$this->middleware(['auth', 'permission:lista-importaciones'])->only('index');
        //$this->middleware(['auth', 'permission:crear-importaciones'])->only(['store']);
        //$this->middleware(['auth', 'permission:editar-importaciones'])->only(['update']);
        //$this->middleware(['auth', 'permission:eliminar-importaciones'])->only(['destroy']);
    }

    public function index()
    {

        return Inertia::render('Importacion/Index', [
            'productos' => new ImportacionCollection(
                Importacion::orderBy('id', 'DESC')
                    ->get()
            )
        ]);
    }

    public function create()
    {
        return Inertia::render('Importacion/Create');
    }

    public function store(ImportacionStoreRequest $request)
    {
        $usuario = auth()->user();
        $file = $request->file('archivo');
        DB::beginTransaction();
        try {

            //creando importacion
            $importacion = Importacion::create([
                'nro_carpeta' => $request->nro_carpeta ?? '',
                'nro_contenedor' => $request->nro_contenedor ?? '',
                'estado' => $request->estado ?? '',
                'total' => $request->total ?? 0,
                'fecha_arribado' => $request->fecha_arribado ?? '',
                'fecha_camino' => $request->fecha_camino ?? '',
                'mueve_stock' => $request->mueve_stock ?? false,
                'user_id' => $usuario->id

            ]);

            //importando excel
            Excel::import(new ImportacionesImport($importacion->id, $importacion->estado, $importacion->mueve_stock), $file);

            //actualizando total

            $importaci = ImportacionDetalle::where('importacion_id', $importacion->id)->get();
            $importacion->update([
                "total" => $importaci->sum('valor_total')
            ]);

            DB::commit();
            return Redirect::route('importaciones.index')->with([
                // 'success' =>  $venta->codigo
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function showModal($id)
    {
        $importacion = DB::table('importaciones as impor')
            ->select(DB::raw("impor.*,DATE_FORMAT(impor.created_at,'%d/%m/%y %H:%i:%s') AS fecha,
        FORMAT(impor.total, 2, 'en_US') as total"))
            ->where('id', $id)->first();
        return response()->json([
            "importacion" => $importacion
        ]);
    }

    public function showProductoModal($id)
    {

        $importacion_detalle = ImportacionDetalle::with(['importacion' => function ($query) {
            $query->select('*');
        }])->with(['producto' => function ($query) {
            $query->select('id', 'nombre', 'codigo_barra', 'origen');
        }])->where('id', $id)->first();
        return response()->json([
            "importacion_detalle" => $importacion_detalle
        ]);
    }

    public function edit($id)
    {
        $importacion = DB::table('importaciones as impor')
            ->select(DB::raw("impor.*,DATE_FORMAT(impor.created_at,'%d/%m/%y %H:%i:%s') AS fecha,
        FORMAT(impor.total, 2, 'en_US') as total"))
            ->where('id', $id)->first();
        //return $importacion;

        $importacion_detalle = DB::table('importaciones_detalles as det')
            ->join('productos as prod', 'prod.codigo_barra', '=', 'det.codigo_barra')
            ->select(DB::raw("det.*,prod.nombre,prod.aduana,prod.imagen,prod.id as producto_id"))
            ->where('importacion_id', $id)->get();

        return Inertia::render('Importacion/Edit', [
            'importacion' => $importacion,
            'importacion_detalle' => $importacion_detalle,
        ]);
    }



    public function updateProducto(ProductoImportacionUpdateRequest $request, $id)
    {

        $importacion_detalle = ImportacionDetalle::find($id);
        $producto = Producto::where('origen', '=', $importacion_detalle->sku)->first();
        $estado = $request->estado;
        DB::beginTransaction();
        try {
            if ($estado == 'Arribado') {

                //quitando cantidad en stock productos
                $sus_stock = $producto->stock - $importacion_detalle->cantidad_total;
                $sus_arribado = $producto->arribado - $importacion_detalle->cantidad_total;

                //agregando
                $add_stock = $sus_stock + $request->cantidad_total;
                $add_arribado = $sus_arribado + $request->cantidad_total;

                $producto->update([

                    "stock" => $add_stock,
                    "arribado" => $add_arribado,
                    "stock_futuro" => $add_stock + $producto->en_camino,
                ]);
                //Guardando producto importacion
                $importacion_detalle->precio = $request->precio;
                $importacion_detalle->unidad = $request->unidad;
                $importacion_detalle->pcs_bulto = $request->pcs_bulto;
                $importacion_detalle->valor_total = $request->valor_total;
                $importacion_detalle->cantidad_total = $request->cantidad_total;
                $importacion_detalle->cbm_bulto = $request->cbm_bulto;
                $importacion_detalle->cbm_total = $request->cbm_total;
                $importacion_detalle->estado = $request->estado;

                $importacion_detalle->save();
            }

            if ($estado == 'En camino') {
                //quitando cantidad en stock productos
                $sus_camino = $producto->en_camino - $importacion_detalle->cantidad_total;
                //agregando
                $add_camino = $sus_camino + $request->cantidad_total;
                $add_stock_futuro =  $producto->stock + $add_camino;

                $producto->update([

                    "en_camino" => $add_camino,
                    "stock_futuro" => $add_stock_futuro,
                ]);
                //Guardando producto importacion
                $importacion_detalle->precio = $request->precio;
                $importacion_detalle->unidad = $request->unidad;
                $importacion_detalle->pcs_bulto = $request->pcs_bulto;
                $importacion_detalle->valor_total = $request->valor_total;
                $importacion_detalle->cantidad_total = $request->cantidad_total;
                $importacion_detalle->cbm_bulto = $request->cbm_bulto;
                $importacion_detalle->cbm_total = $request->cbm_total;
                $importacion_detalle->estado = $request->estado;

                $importacion_detalle->save();
            }
            DB::commit();
            return Redirect::route('importaciones.index')->with([
                // 'success' =>  $venta->codigo
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function update(ImportacionUpdateRequest $request, $id)
    {
        $importacion = Importacion::find($id);
        $estado = $request->estado;

        DB::beginTransaction();
        try {
            if ($estado != $importacion->estado) {
            if ($estado == 'Arribado') {
                //creando detalle importacion
                foreach ($importacion->importaciones_detalles as $detalle) {
                    $codigo = $detalle->sku;
                    $producto = Producto::where('origen', '=', $codigo)
                        ->first();
                    $new_encamino = $producto->en_camino - $detalle->cantidad_total;
                    $new_arribado = $producto->arribado + $detalle->cantidad_total;
                    $new_stock = $producto->stock + $detalle->cantidad_total;
                    $new_futuro = $new_stock + $new_encamino;
                    $producto->update([
                    "stock" => $new_stock,
                    "arribado" => $new_arribado,
                    "en_camino" => $new_encamino,
                    "stock_futuro" => $new_futuro,
                ]);
                }
            }
            if ($estado == 'En camino') {
                //actualizando stock productos
                foreach ($importacion->importaciones_detalles as $detalle) {
                    $codigo = $detalle->sku;
                    $producto = Producto::where('origen', '=', $codigo)
                        ->first();

                    $new_arribado = $producto->arribado - $detalle->cantidad_total;
                    $new_encamino = $producto->en_camino + $detalle->cantidad_total;
                    $new_stock = $producto->stock - $detalle->cantidad_total;
                    $new_futuro = $new_stock + $new_encamino;

                    $producto->update([
                        "en_camino" => $new_encamino,
                        "stock" => $new_stock,
                        "arribado" => $new_arribado,
                        "stock_futuro" => $new_futuro,
                    ]);
                }
            }
            }

            $importacion->nro_carpeta = $request->input('nro_carpeta');
            $importacion->nro_contenedor = $request->input('nro_contenedor');
            $importacion->estado = $request->input('estado');
            $importacion->fecha_arribado     = $request->input('fecha_arribado');
            $importacion->fecha_camino = $request->input('fecha_camino');
            $importacion->save();

            DB::commit();
            return Redirect::route('importaciones.index')->with([
                // 'success' =>  $venta->codigo
            ]);
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

        $importacion = DB::table('importaciones as impor')
            ->select(DB::raw("impor.*,DATE_FORMAT(impor.created_at,'%d/%m/%y %H:%i:%s') AS fecha,
        FORMAT(impor.total, 2, 'en_US') as total"))
            ->where('id', $id)->first();
        //return $importacion;

        $importacion_detalle = DB::table('importaciones_detalles as det')
            ->join('productos as prod', 'prod.codigo_barra', '=', 'det.codigo_barra')
            ->select(DB::raw("det.*,prod.nombre,prod.aduana,prod.imagen,prod.id as producto_id"))
            ->where('importacion_id', $id)->get();

        return Inertia::render('Importacion/Show', [
            'importacion' => $importacion,
            'importacion_detalle' => $importacion_detalle,
        ]);
    }

    public function destroy($id)
    {
        $importacion = Importacion::find($id);
        $estado = $importacion->estado;

        if ($estado == 'Arribado') {
            //creando detalle venta
            foreach ($importacion->importaciones_detalles as $detalle) {
                $codigo = $detalle->sku;
                $producto = Producto::where('origen', '=', $codigo)
                    ->first();
                $new_stock = $producto->stock - $detalle->cantidad_total;
                $new_arribado = $producto->arribado - $detalle->cantidad_total;

                $new_futuro = $new_stock + $producto->en_camino;
                if ($producto->arribado >0) {
                $producto->update([
                    "stock" => $new_stock,
                    "arribado" => $new_arribado,
                    "stock_futuro" => $new_futuro,
                ]);
            }
            }
        }
        if ($estado == 'En camino') {
            //creando detalle venta
            foreach ($importacion->importaciones_detalles as $detalle) {
                $codigo = $detalle->sku;
                $producto = Producto::where('origen', '=', $codigo)
                    ->first();
                $new_camino = $producto->en_camino - $detalle->cantidad_total;
                $new_futuro =  $producto->stock + $new_camino;
                if ($producto->en_camino >0) {
                $producto->update([
                    "en_camino" => $new_camino,
                    "stock_futuro" => $new_futuro,
                ]);
            }
            }
        }

        $importacion->importaciones_detalles()->delete();
        $importacion->delete();
    }

    public function exportExcel($id)
    {
        return Excel::download(new ImportacionesExport($id), 'ImportacionExcel.xlsx');
    }
}
