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
use Carbon\Carbon;

class RotacionStockController extends Controller
{

    public function index()
    {


       $primerDiaMes = Carbon::now()->startOfMonth()->toDateString();
        $ultimoDiaMes = Carbon::now()->endOfMonth()->toDateString();

      /*  $consulta_ventas = DB::table('ventas as ve')
        ->select(DB::raw("ve.created_at, SUM(ve.total) AS total,DATE_FORMAT(ve.created_at,'%d/%m/%y') AS fecha"))
        ->when(Request::input('inicio'), function ($query, $search) {
            $query->whereDate('ve.created_at', '>=', $search);
        })
        ->when(Request::input('fin'), function ($query, $search) {
            $query->whereDate('ve.created_at', '<=', $search);
        })
        ->whereDate('ve.created_at', '>=', $primerDiaMes)
        ->whereDate('ve.created_at', '<=', $ultimoDiaMes)
        ->where('ve.tipo', '=', 'VENTA')
        ->where('ve.facturado', '=', '1')
        ->orderBy('fecha', 'asc')
        ->groupBy('fecha')
        ->get();*/


        /*$consulta_ventas= Producto::with(['detalles_ventas' => function ($query)  use ($primerDiaMes,$ultimoDiaMes){
            $query->select('*',DB::raw("count('producto_id') as sku"))
            ->with(['venta' => function ($query)  use ($primerDiaMes,$ultimoDiaMes){
                $query
                ->select('*',DB::raw("id,nro_compra,destino,created_at,
                DATE_FORMAT(created_at ,'%d/%m/%Y') AS fecha"))->whereDate('created_at', '>=', $primerDiaMes)
                ->whereDate('created_at', '<=', $ultimoDiaMes);
            }]);
        }])->select("*")
        ->orderBy('id', 'ASC')->get();*/

        /*$consulta_ventas = DB::table('ventas as ve')
        ->join('venta_detalles as det', 've.id', '=', 'det.venta_id')
        ->join('productos as prod', 'prod.id', '=', 'det.producto_id')
        ->select(
            'prod.origen',
            'prod.id',
            've.created_at',
            'det.precio',
            'det.producto_validado',
            'det.cantidad',
            've.fecha_facturacion',
            'prod.nombre'
        )*/
        //->whereDate('ve.fecha_facturacion', '>=', $primerDiaMes)
        //->select('ve.*')
        //->whereDate('ve.fecha_facturacion', '>=', '2023-09-10')

        //->whereDate('ve.fecha_facturacion', '<=', $ultimoDiaMes)
       // ->whereDate('ve.fecha_facturacion', '<=', '2023-09-11')

        //->where('prod.id', '=', $id)
        //->where('det.producto_validado', 1)
        //->orderBy('ve.created_at', 'DESC')->get();

      /*  $consulta_ventas= DB::table('ventas as ve')
        ->join('venta_detalles as det', 've.id', '=', 'det.venta_id')
        ->join('productos as prod', 'prod.id', '=', 'det.producto_id')
        ->select(
            //'det.cantidad',
        'prod.stock','prod.stock_futuro','prod.origen','prod.nombre','ve.fecha_facturacion',
        've.id','ve.facturado','ve.validado',
        //DB::raw("sum(det.cantidad) as total")
        )
        ->whereDate('ve.fecha_facturacion', '>=', '2023-09-12')
         ->whereDate('ve.fecha_facturacion', '<=', '2023-09-22')
         ->where('ve.facturado',1)
        //DB::Raw('count(det.producto_id) as sumaTotal' )
        ->where('det.producto_id','=','1519')
    ->get();*/







    $consulta_ventas=DB::table('venta_detalles as det')
    ->join('ventas as ve', 'det.venta_id', '=', 've.id')
    ->join('productos as prod', 'prod.id', '=', 'det.producto_id')
    ->whereDate('ve.fecha_facturacion', '>=', '2023-09-14')
    ->whereDate('ve.fecha_facturacion', '<=', '2023-09-14')
    ->select('prod.nombre','prod.origen','prod.stock','prod.stock_futuro','det.cantidad',
    'prod.id','det.producto_id',DB::raw("DATE_FORMAT(ve.fecha_facturacion,'%d/%m/%y') AS fecha"))
    //->where('det.producto_id','=','1513')
    //->where('det.producto_id','=','prod.id')
    ->groupBy('prod.id')
    ->get()
    ;
    return $consulta_ventas;

        return Inertia::render('RotacionStock/Index', [
            'productos' => new ProductoCollection(
                Producto::orderBy('id', 'DESC')
                    ->get()
            )
        ]);
    }




    public function show($id)
    {
        $producto=Producto::with(['detalles_ventas' => function ($query) {
            $query->select(DB::raw("*"))
            ->with(['venta' => function ($query) {
                $query->select(DB::raw("id,nro_compra,destino,
                DATE_FORMAT(created_at ,'%d/%m/%Y') AS fecha"));
            }]);
        }])->with(['importacion_detalles' => function ($query) {
            $query->select(DB::raw("*"))
            ->with(['importacion' => function ($query) {
                $query->select(DB::raw("id,nro_carpeta,nro_contenedor,
                DATE_FORMAT(fecha_arribado ,'%d/%m/%Y') AS fecha"));
            }]);
        }])->select(DB::raw("productos.*"))
        ->orderBy('id', 'ASC')->findOrFail($id);

        //return $producto;
        $productoImportacion=DB::table('importaciones as imp')
        ->join('importaciones_detalles as det', 'imp.id', '=', 'det.importacion_id')
        ->join('productos as prod', 'prod.origen', '=', 'det.sku')
        ->select('imp.nro_carpeta','imp.nro_contenedor','imp.nro_contenedor',
         'det.precio','det.pcs_bulto','det.bultos','det.pcs_bulto','det.cantidad_total','det.valor_total',
         'det.cbm_bulto','det.cbm_total','det.importacion_id',DB::raw("DATE_FORMAT(imp.fecha_arribado ,'%d/%m/%Y') AS fecha_arribado")
         )->where('prod.id','=',$id)
        ->get();
        $cantidad=VentaDetalle::where('producto_id',$id)->sum('cantidad');
        $cantidad_importacion=ImportacionDetalle::where('sku',$producto->origen)->sum('cantidad_total');
        $tipo_cambio_yuan=TipoCambioYuan::latest()->first();
        $ultimo_importacion=ImportacionDetalle::select('precio')->where('sku',$producto->origen)->latest()->first();
        $ultimo_precio=0;
        $ultimo_yang=0;
        $costo_aprox=0.0;
        if(!empty($ultimo_importacion)){
            $ultimo_precio=$ultimo_importacion->precio;
            if(!empty($tipo_cambio_yuan)){
                $ultimo_yang=$tipo_cambio_yuan->valor;
                $costo_aprox=$ultimo_precio*1.70/$ultimo_yang;

            }

        }


        //return $tipo_cambio_yuan;
        return Inertia::render('Producto/Show', [
            'producto' => $producto,
            'productoImportacion' => $productoImportacion,
            'cantidad' => $cantidad,
            'costo_aprox' =>number_format( $costo_aprox,2,','),
            'ultimo_yang' => $ultimo_yang,
            'cantidad_importacion' => $cantidad_importacion,
        ]);
    }



    public function productoVenta($id,$inicio,$fin){

        $producto=Producto::with(['detalles_ventas' => function ($query) use ($inicio,$fin) {
            $query->select(DB::raw("*"))
            ->with(['venta' => function ($query) {
                $query
                ->select('*',DB::raw("id,nro_compra,destino,created_at,
                DATE_FORMAT(created_at ,'%d/%m/%Y') AS fecha"));
            }])->whereDate('created_at', '>=', $inicio)
            ->whereDate('created_at', '<=', $fin);
        }])->select(DB::raw("productos.*"))
        ->orderBy('id', 'ASC')->findOrFail($id);
        $cantidad=VentaDetalle::where('producto_id',$id)->whereDate('created_at', '>=', $inicio)
        ->whereDate('created_at', '<=', $fin)->sum('cantidad');
        return response()->json([
            'producto' => $producto,
            'cantidad' => $cantidad,
        ]);
    }

    public function productoImportacion($id,$inicio,$fin){


        $producto=DB::table('importaciones as imp')
        ->join('importaciones_detalles as det', 'imp.id', '=', 'det.importacion_id')
        ->join('productos as prod', 'prod.origen', '=', 'det.sku')
        ->select('imp.nro_carpeta','imp.nro_contenedor','imp.nro_contenedor','imp.fecha_arribado',
         'det.precio','det.sku','det.unidad','det.pcs_bulto','det.bultos','det.pcs_bulto','det.cantidad_total','det.valor_total',
         'det.cbm_bulto','det.cbm_total','det.importacion_id',DB::raw("DATE_FORMAT(imp.fecha_arribado ,'%d/%m/%Y') AS fecha_arribado"),
         'prod.origen','prod.id'
         )
        ->whereDate('imp.fecha_arribado', '>=', $inicio)
            ->whereDate('imp.fecha_arribado', '<=', $fin)
            ->where('prod.id','=',$id)
        ->orderBy('id', 'ASC')->get();
        $origen=Producto::findOrFail($id);

        $cantidad_importacion=$producto->where('sku',$origen->origen)->sum('cantidad_total');
        return response()->json([
            'producto' => $producto,
            'cantidad_importacion' => $cantidad_importacion,
        ]);
    }

    public function exportProductoVentas($id)
    {
        $producto=DB::table('ventas as ve')
        ->join('venta_detalles as det', 've.id', '=', 'det.venta_id')
        ->join('productos as prod', 'prod.id', '=', 'det.producto_id')
        ->select(DB::raw("DATE_FORMAT(ve.created_at ,'%d/%m/%Y') AS fecha"),
         'prod.origen','prod.id','det.precio','det.cantidad','ve.destino','ve.nro_compra','prod.nombre'
         )
         ->when(Request::input('inicio'), function ($query) {
            $query->whereDate('ve.created_at', '>=', Request::input('inicio'));
        })
        ->when(Request::input('inicio'), function ($query) {
            $query->whereDate('ve.created_at', '<=', Request::input('fin'));
        })
            ->where('prod.id','=',$id)
        ->orderBy('ve.created_at', 'DESC')->get();

        return Excel::download(new ProductoVentaExport($producto), 'ProductoVentas.xlsx');
    }

}
