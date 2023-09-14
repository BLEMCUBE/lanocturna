<?php

namespace App\Http\Controllers;

use App\Exports\ProductosExport;
use App\Http\Requests\ProductoImportRequest;
use App\Http\Requests\ProductoStoreRequest;
use App\Http\Requests\ProductoUpdateRequest;
use App\Http\Resources\ProductoCollection;
use App\Imports\ProductoImport;
use App\Models\ImportacionDetalle;
use App\Models\Producto;
use App\Models\VentaDetalle;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ProductoController extends Controller
{
    public function __construct()
    {
        //protegiendo el controlador segun el rol
        $this->middleware(['auth', 'permission:lista-productos'])->only('index');
        $this->middleware(['auth', 'permission:crear-productos'])->only(['store']);
        $this->middleware(['auth', 'permission:editar-productos'])->only(['update']);
        $this->middleware(['auth', 'permission:eliminar-productos'])->only(['destroy']);
    }

    public function index()
    {
        return Inertia::render('Producto/Index', [
            'productos' => new ProductoCollection(
                Producto::orderBy('id', 'DESC')
                    ->get()
            )
        ]);
    }

    public function create()
    {
        return Inertia::render('Producto/Create');
    }

    public function store(ProductoStoreRequest $request)
    {
        $producto = Producto::create($request->input());
        //imagen
        if ($request->hasFile('photo')) {
            sleep(1);
            $fileName = time() . '.' . $request->photo->extension();
            $producto->update([
                'imagen' => "/images/productos/" . $fileName
            ]);
            $request->photo->move(public_path('images/productos'), $fileName);
        } else {
            $producto->update([
                'imagen' => "/images/productos/sin_foto.png"
            ]);
        }

    }

    public function edit($id){
        $producto = Producto::findOrFail($id);
        return Inertia::render('Producto/Edit', [
            'producto' => $producto
        ]);

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
        $producto->stock_futuro = $producto->en_camino+$request->input('stock');
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
        $producto=Producto::with(['detalles_ventas' => function ($query) {
            $query->select(DB::raw("*"))
            ->with(['venta' => function ($query) {
                $query->select(DB::raw("id,nro_compra,destino,
                DATE_FORMAT(created_at ,'%d/%m/%Y %H:%i:%s') AS fecha"));
            }]);
        }])->with(['importacion_detalles' => function ($query) {
            $query->select(DB::raw("*"))
            ->with(['importacion' => function ($query) {
                $query->select(DB::raw("id,nro_carpeta,nro_contenedor,
                DATE_FORMAT(created_at ,'%d/%m/%Y %H:%i:%s') AS fecha"));
            }]);
        }])->select(DB::raw("productos.*"))
        ->orderBy('id', 'ASC')->findOrFail($id);
        $cantidad=VentaDetalle::where('producto_id',$id)->sum('cantidad');

        $cantidad_importacion=ImportacionDetalle::where('codigo_barra',$producto->codigo_barra)->sum('cantidad_total');
        return Inertia::render('Producto/Show', [
            'producto' => $producto,
            'cantidad' => $cantidad,
            'cantidad_importacion' => $cantidad_importacion,
        ]);
    }

    public function destroy($id)
    {
        $producto = Producto::find($id);
        $old_photo = $producto->imagen;
        $url_save = public_path() . $old_photo;

        //eliminar imagen si existe
        if (file_exists($url_save) && $old_photo != "/images/productos/sin_foto.png") {
            unlink($url_save);
        }
        $producto->delete();
    }

    public function exportExcel()
    {
        return Excel::download(new ProductosExport(), 'productos.xlsx');
    }
    public function vistaImportar()
    {
        return Inertia::render('Producto/Importar');
    }
    public function importExcel(ProductoImportRequest $request)
    {
        $file = $request->file('archivo');
           //importando excel
           Excel::import(new ProductoImport(), $file);

    }

    public function actualizarFuturo()
    {

        $productos=Producto::all();

        foreach ($productos as $producto) {
          $act=   Producto::where('id', '=', $producto->id)->first();
          $stock_act=0;
          if($act->stock<=0){
            $stock_act=0;
          }else{
            $stock_act=$act->stock;
          }
          $act->update([
            "stock"=>$stock_act,
            "stock_futuro"=>$stock_act
          ]);
        }
        return 'Stock futuro Actualizado';
    }


}
