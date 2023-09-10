<?php

namespace App\Http\Controllers;

use App\Exports\ImportacionesExport;
use App\Http\Requests\ImportacionStoreRequest;
use App\Http\Requests\ProductoUpdateRequest;
use App\Http\Resources\ImportacionCollection;
use App\Http\Resources\ProductoCollection;
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
        $this->middleware(['auth', 'permission:lista-importaciones'])->only('index');
        $this->middleware(['auth', 'permission:crear-importaciones'])->only(['store']);
        $this->middleware(['auth', 'permission:editar-importaciones'])->only(['update']);
        $this->middleware(['auth', 'permission:eliminar-importaciones'])->only(['destroy']);
    }

    public function index()
    {

        return Inertia::render('Importacion/Index', [
            'productos' =>new ImportacionCollection(
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
        $file=$request->file('archivo');
        DB::beginTransaction();
        try {

             //creando importacion
             $importacion = Importacion::create([
                'nro_carpeta' => $request->nro_carpeta??'',
                'nro_contenedor' => $request->nro_contenedor ?? '',
                'estado' => $request->estado ?? '',
                'total' => $request->total ?? 0,
                'fecha_arribado'=>$request->fecha_arribado??'',
                'fecha_camino'=>$request->fecha_camino??'',
                'mueve_stock'=>$request->mueve_stock??false,
                'user_id' => $usuario->id

            ]);

            //importando excel
            Excel::import(new ImportacionesImport($importacion->id,$importacion->estado,$importacion->mueve_stock), $file);

            //actualizando total

            $importaci = ImportacionDetalle::where('importacion_id',$importacion->id)->get();
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

    public function edit($id){
        return Redirect::route('importaciones.index');

        /*$producto = Producto::findOrFail($id);
        return Inertia::render('Importacion/Edit', [
            'producto' => $producto
        ]);*/

    }

    public function update(ProductoUpdateRequest $request,$id){
        $producto = Producto::find($id);
        $old_photo = $producto->imagen;
        $producto->origen = $request->input('origen');
        $producto->nombre = $request->input('nombre');
        $producto->aduana = $request->input('aduana');
        $producto->codigo_barra     = $request->input('codigo_barra');
        $producto->stock = $request->input('stock');
        $producto->stock_minimo = $request->input('stock_minimo');
        $producto->save();

        //imagen
           if ($request->hasFile('photo')) {
            sleep(1);
            $url_save = public_path() . $old_photo;
            $fileName = time() . '.' . $request->photo->extension();
                //eliminar imagen
                if (file_exists($url_save) && $old_photo != "/images/productos/sin_foto.png") {
                    unlink($url_save);
                }
            $producto->update([
                'imagen' => "/images/productos/" . $fileName
            ]);
            $request->photo->move(public_path('images/productos'), $fileName);
        }
    }

    public function show($id)
    {

        $importacion=DB::table('importaciones as impor')
        ->select(DB::raw("impor.*,DATE_FORMAT(impor.created_at,'%d/%m/%y %H:%i:%s') AS fecha,
        FORMAT(impor.total, 2, 'en_US') as total"))
        ->where('id',$id)->first();
        //return $importacion;

        $importacion_detalle=DB::table('importaciones_detalles as det')
        ->join('productos as prod', 'prod.codigo_barra', '=', 'det.codigo_barra')
        ->select(DB::raw("det.*,prod.nombre,prod.aduana,prod.imagen,prod.id as producto_id"))
        ->where('importacion_id',$id)->get();

        return Inertia::render('Importacion/Show', [
            'importacion' => $importacion,
            'importacion_detalle' => $importacion_detalle,
        ]);
    }

    public function destroy($id)
    {
        $importacion = Importacion::find($id);
        $detalle = ImportacionDetalle::where('importacion_id',$id);
        $importacion->delete();
        $detalle->delete();

    }

    public function exportExcel($id)
    {
        //$importacion = Importacion::find($id);
        //return $importacion;
        return Excel::download(new ImportacionesExport($id), 'ImportacionExcel.xlsx');
        //$detalle = ImportacionDetalle::where('importacion_id',$id);
        //$importacion->delete();
        //$detalle->delete();

    }


}
