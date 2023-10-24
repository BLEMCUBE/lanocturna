<?php

namespace App\Http\Controllers;

use App\Exports\ProductosExport;
use App\Exports\ProductoVentaExport;
use App\Http\Requests\ProductoImportRequest;
use App\Http\Requests\ProductoStoreRequest;
use App\Http\Requests\ProductoUpdateRequest;
use App\Http\Resources\ProductoCollection;
use App\Imports\ProductoImport;
use App\Models\ImportacionDetalle;
use App\Models\Producto;
use App\Models\TipoCambioYuan;
use App\Models\VentaDetalle;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Support\Facades\Request;


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

        $new_stock =  $producto->stock;
        $producto->update([
            "stock" => $new_stock,
            "stock_futuro" => $new_stock + $producto->en_camino
        ]);
    }

    public function edit($id)
    {
        $producto = Producto::findOrFail($id);
        return Inertia::render('Producto/Edit', [
            'producto' => $producto
        ]);
    }

    public function update(ProductoUpdateRequest $request, $id)
    {
        $producto = Producto::find($id);
        $old_photo = $producto->imagen;
        $producto->origen = $request->input('origen');
        $producto->nombre = $request->input('nombre');
        $producto->aduana = $request->input('aduana');
        $producto->codigo_barra     = $request->input('codigo_barra');
        $producto->stock = $request->input('stock');
        $producto->stock_minimo = $request->input('stock_minimo');
        $producto->stock_futuro = $producto->en_camino + $request->input('stock');
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
        $new_stock =  $producto->stock;
        $producto->update([
            "stock" => $new_stock,
            "stock_futuro" => $new_stock + $producto->en_camino
        ]);
    }


    public function show($id)
    {
        $producto = Producto::with(['detalles_ventas' => function ($query) {
            $query->select(DB::raw("*"))
                ->with(['venta' => function ($query) {
                    $query->select(DB::raw("id,nro_compra,destino,facturado,
                DATE_FORMAT(created_at ,'%d/%m/%Y') AS fecha"));
                }])->where('producto_validado', 1);
        }])->with(['importacion_detalles' => function ($query) {
            $query->select(DB::raw("*"))
                ->with(['importacion' => function ($query) {
                    $query->select(DB::raw("id,nro_carpeta,nro_contenedor,
                DATE_FORMAT(fecha_arribado ,'%d/%m/%Y') AS fecha"));
                }]);
        }])->select(DB::raw("productos.*"))
            ->orderBy('id', 'ASC')->findOrFail($id);

        //return $producto;
        $productoImportacion = DB::table('importaciones as imp')
            ->join('importaciones_detalles as det', 'imp.id', '=', 'det.importacion_id')
            ->join('productos as prod', 'prod.origen', '=', 'det.sku')
            ->select(
                'imp.nro_carpeta',
                'imp.nro_contenedor',
                'det.precio',
                'det.pcs_bulto',
                'det.bultos',
                'det.pcs_bulto',
                'det.cantidad_total',
                'det.valor_total',
                'det.cbm_bulto',
                'det.cbm_total',
                'det.importacion_id',
                DB::raw("DATE_FORMAT(imp.fecha_arribado ,'%d/%m/%Y') AS fecha_arribado")
            )->where('prod.id', '=', $id)
            ->orderBy('imp.fecha_arribado', 'DESC')->get();

        $productoventa = DB::table('ventas as ve')
            ->join('venta_detalles as det', 've.id', '=', 'det.venta_id')
            ->join('productos as prod', 'prod.id', '=', 'det.producto_id')
            ->select(
                DB::raw("DATE_FORMAT(ve.created_at ,'%d/%m/%Y') AS fecha"),
                'prod.origen',
                'prod.id',
                've.created_at',
                'det.precio',
                'det.producto_validado',
                'det.cantidad',
                've.destino',
                'det.venta_id',
                've.nro_compra',
                'prod.nombre'
            )
            ->where('prod.id', '=', $id)->where('det.producto_validado', 1)
            ->orderBy('ve.created_at', 'DESC')->get();

        $cantidad = VentaDetalle::where('producto_id', $id)->sum('cantidad');
        $cantidad_importacion = ImportacionDetalle::where('sku', $producto->origen)->sum('cantidad_total');
        $tipo_cambio_yuan = TipoCambioYuan::latest()->first();
        $ultimo_importacion = ImportacionDetalle::select('precio')->where('sku', $producto->origen)->latest()->first();

        $costo_aprox = 0;
        $ultimo_yang = 0;

        if (!is_null($ultimo_importacion)) {

            $ultimo_precio = $ultimo_importacion->precio;
        } else {
            $ultimo_precio = 0;
        }
        if (!is_null($tipo_cambio_yuan)) {

            $ultimo_yang = $tipo_cambio_yuan->valor;
            $costo_aprox = $ultimo_precio * 1.70 / $ultimo_yang;
        } else {
            $ultimo_yang = 0;
        }


        return Inertia::render('Producto/Show', [
            'producto' => $producto,
            'productoImportacion' => $productoImportacion,
            'cantidad' => $cantidad,
            'costo_aprox' => number_format($costo_aprox, 2, ','),
            'ultimo_yang' => $ultimo_yang,
            'productoventa' => $productoventa,
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

        $productos = Producto::all();

        foreach ($productos as $producto) {
            $act =   Producto::where('id', '=', $producto->id)->first();
            $stock_act = 0;
            if ($act->stock <= 0) {
                $stock_act = 0;
            } else {
                $stock_act = $act->stock;
            }
            $act->update([
                "stock" => $stock_act,
                "stock_futuro" => $stock_act
            ]);
        }
        return 'Stock futuro Actualizado';
    }


    public function productoVenta($id, $inicio, $fin)
    {

        $productoventa = DB::table('ventas as ve')
            ->join('venta_detalles as det', 've.id', '=', 'det.venta_id')
            ->join('productos as prod', 'prod.id', '=', 'det.producto_id')
            ->select(
                //DB::raw("DATE_FORMAT(ve.created_at ,'%d/%m/%Y') AS fecha"),
                'prod.origen',
                'prod.id',
                've.created_at',
                'det.precio',
                'det.producto_validado',
                'det.cantidad',
                've.destino',
                'det.venta_id',
                've.nro_compra',
                'prod.nombre'
            )
            ->whereDate('ve.created_at', '>=', $inicio)

            ->whereDate('ve.created_at', '<=', $fin)

            ->where('prod.id', '=', $id)->where('det.producto_validado', 1)
            ->orderBy('ve.created_at', 'DESC')->get();

        $cantidad = VentaDetalle::where('producto_id', $id)->whereDate('created_at', '>=', $inicio)
            ->whereDate('created_at', '<=', $fin)->sum('cantidad');

        return response()->json([
            'productoventa' => $productoventa,
            'cantidad' => $cantidad,
        ]);
    }

    public function productoImportacion($id, $inicio, $fin)
    {


        $producto = DB::table('importaciones as imp')
            ->join('importaciones_detalles as det', 'imp.id', '=', 'det.importacion_id')
            ->join('productos as prod', 'prod.origen', '=', 'det.sku')
            ->select(
                'imp.nro_carpeta',
                'imp.nro_contenedor',
                'imp.nro_contenedor',
                'imp.fecha_arribado',
                'det.precio',
                'det.sku',
                'det.unidad',
                'det.pcs_bulto',
                'det.bultos',
                'det.pcs_bulto',
                'det.cantidad_total',
                'det.valor_total',
                'det.cbm_bulto',
                'det.cbm_total',
                'det.importacion_id',
                DB::raw("DATE_FORMAT(imp.fecha_arribado ,'%d/%m/%Y') AS fecha_arribado"),
                'prod.origen',
                'prod.id'
            )
            ->whereDate('imp.fecha_arribado', '>=', $inicio)
            ->whereDate('imp.fecha_arribado', '<=', $fin)
            ->where('prod.id', '=', $id)
            ->orderBy('id', 'ASC')->get();
        $origen = Producto::findOrFail($id);

        $cantidad_importacion = $producto->where('sku', $origen->origen)->sum('cantidad_total');
        return response()->json([
            'producto' => $producto,
            'cantidad_importacion' => $cantidad_importacion,
        ]);
    }

    public function exportProductoVentas($id)
    {
        $producto = DB::table('ventas as ve')
            ->join('venta_detalles as det', 've.id', '=', 'det.venta_id')
            ->join('productos as prod', 'prod.id', '=', 'det.producto_id')
            ->select(
                DB::raw("DATE_FORMAT(ve.created_at ,'%d/%m/%Y') AS fecha"),
                'prod.origen',
                'prod.id',
                'det.precio',
                'det.cantidad',
                've.destino',
                've.nro_compra',
                'prod.nombre'
            )
            ->when(Request::input('inicio'), function ($query) {
                $query->whereDate('ve.created_at', '>=', Request::input('inicio'));
            })
            ->when(Request::input('inicio'), function ($query) {
                $query->whereDate('ve.created_at', '<=', Request::input('fin'));
            })
            ->where('prod.id', '=', $id)
            ->orderBy('ve.created_at', 'DESC')->get();

        return Excel::download(new ProductoVentaExport($producto), 'ProductoVentas.xlsx');
    }
}
