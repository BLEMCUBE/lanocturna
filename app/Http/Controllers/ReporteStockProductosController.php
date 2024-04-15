<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Arr;
use Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReporteStockProductosController extends Controller
{
    public function index()
    {

        $query_total_ventas = DB::table('venta_detalles as det')
            ->join('ventas as ve', 'det.venta_id', '=', 've.id')
            ->join('productos as prod', 'prod.id', '=', 'det.producto_id')
            ->when(Request::input('inicio'), function ($query) {
                $query->whereDate('ve.fecha_facturacion', '>=', Request::input('inicio'));
            })
            ->when(Request::input('fin'), function ($query) {
                $query->whereDate('ve.fecha_facturacion', '<=', Request::input('fin'));
            })
            ->where('det.producto_validado', '=', 1)
            ->select(
                'prod.nombre',
                'prod.origen',
                //'prod.codigo_barra',
                'prod.stock AS stock_actual',
                'det.producto_id',
                DB::raw("sum(det.cantidad) as ventas_totales")
            )
            ->groupBy('prod.id')
            ->get();

        $total_cantidad = 0;
        $total_cantidad = array_sum(array_column($query_total_ventas->toArray(), 'ventas_totales'));
        $ultimas_ventas = [];

        foreach ($query_total_ventas as $vent) {

            $query_total_import = DB::table('importaciones_detalles as imd')
                ->join('importaciones as im', 'imd.importacion_id', '=', 'im.id')
                ->join('productos as prod', 'prod.origen', '=', 'imd.sku')
                ->when(Request::input('inicio'), function ($query) {
                    $query->whereDate('im.fecha_arribado', '>=', Request::input('inicio'));
                })
                ->when(Request::input('fin'), function ($query) {
                    $query->whereDate('im.fecha_arribado', '<=', Request::input('fin'));
                })
                ->where('im.estado', '=', 'Arribado')
                ->where('prod.origen', '=', $vent->origen)
                ->select(
                    'imd.sku',
                    DB::raw("GROUP_CONCAT( im.nro_carpeta SEPARATOR ';' ) as carpetas"),
                    DB::raw("SUM(imd.pcs_bulto) as importaciones_totales")
                )
                ->groupBy('prod.origen')
                ->first();
            $totales_importacion = 0;
            if (!is_null($query_total_import)) {

                $totales_importacion = $query_total_import->importaciones_totales;
            }

            array_push($ultimas_ventas, [
                "id" => $vent->producto_id,
                "sku" => $vent->origen,
                "nombre" => $vent->nombre,
                //"totales_importacion" => $totales_importacion,
                //"codigo_barra" => $vent->codigo_barra,
                //"stock_actual" => $vent->stock_actual,
                "resultado_final" => $vent->stock_actual + $vent->ventas_totales - $totales_importacion,
                //"ventas_totales" => $vent->ventas_totales,
                "total_productos" => round(($vent->ventas_totales / $total_cantidad) * 100, 2),
            ]);
        }

        $total_productos = array_values(Arr::sortDesc($ultimas_ventas, function (array $value) {
            return $value['total_productos'];
        }));

        return Inertia::render('Reporte/StockProductos', [
            //'total_cantidad' => $total_cantidad,
            'total_productos' => $total_productos,

        ]);
    }

    public function exportXls()
    {

        $fecha_inicio = Carbon::parse(Request::input('inicio'));
        $fecha_fin = Carbon::parse(Request::input('fin'));

        $filename = "STOCK_PRODUCTOS_POR_FECHA_" . $fecha_inicio->format('d_m_Y') . ".xlsx";

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', "FECHA: ");
        $sheet->setCellValue('B1', $fecha_inicio->format('d/m/Y'));
        $sheet->getStyle('A' . (string)1 . ':' . 'B' . (string)1)->getFont()->setBold(true);
        $sheet->getStyle('A' . (string)1 . ':' . 'B' . (string)1)->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A' . (string)1 . ':' . 'A' . (string)1)->getAlignment()->setVertical('center');
        $f = 3;

        $sheet->setCellValue('A' . (string)$f, "SKU");
        $sheet->setCellValue('B' . (string)$f, "NOMBRE");
        $sheet->setCellValue('C' . (string)$f, "STOCK");

        $sheet->getStyle('A' . (string)3 . ':' . 'C' . (string)3)->getFont()->setBold(true);
        $sheet->getStyle('A' . (string)3 . ':' . 'C' . (string)3)->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A' . (string)3 . ':' . 'C' . (string)3)->getAlignment()->setVertical('center');

        //datos
        $query_total_ventas = DB::table('venta_detalles as det')
        ->join('ventas as ve', 'det.venta_id', '=', 've.id')
        ->join('productos as prod', 'prod.id', '=', 'det.producto_id')
        ->when(Request::input('inicio'), function ($query) {
            $query->whereDate('ve.fecha_facturacion', '>=', Request::input('inicio'));
        })
        ->when(Request::input('fin'), function ($query) {
            $query->whereDate('ve.fecha_facturacion', '<=', Request::input('fin'));
        })
        ->where('det.producto_validado', '=', 1)
        ->select(
            'prod.nombre',
            'prod.origen',
            //'prod.codigo_barra',
            'prod.stock AS stock_actual',
            'det.producto_id',
            DB::raw("sum(det.cantidad) as ventas_totales")
        )
        ->groupBy('prod.id')
        ->get();

    $total_cantidad = 0;
    $total_cantidad = array_sum(array_column($query_total_ventas->toArray(), 'ventas_totales'));
    $ultimas_ventas = [];

    foreach ($query_total_ventas as $vent) {

        $query_total_import = DB::table('importaciones_detalles as imd')
            ->join('importaciones as im', 'imd.importacion_id', '=', 'im.id')
            ->join('productos as prod', 'prod.origen', '=', 'imd.sku')
            ->when(Request::input('inicio'), function ($query) {
                $query->whereDate('im.fecha_arribado', '>=', Request::input('inicio'));
            })
            ->when(Request::input('fin'), function ($query) {
                $query->whereDate('im.fecha_arribado', '<=', Request::input('fin'));
            })
            ->where('im.estado', '=', 'Arribado')
            ->where('prod.origen', '=', $vent->origen)
            ->select(
                'imd.sku',
                DB::raw("GROUP_CONCAT( im.nro_carpeta SEPARATOR ';' ) as carpetas"),
                DB::raw("SUM(imd.pcs_bulto) as importaciones_totales")
            )
            ->groupBy('prod.origen')
            ->first();
        $totales_importacion = 0;
        if (!is_null($query_total_import)) {

            $totales_importacion = $query_total_import->importaciones_totales;
        }

        array_push($ultimas_ventas, [
            "id" => $vent->producto_id,
            "sku" => $vent->origen,
            "nombre" => $vent->nombre,
            "resultado_final" => $vent->stock_actual + $vent->ventas_totales - $totales_importacion,
            "total_productos" => round(($vent->ventas_totales / $total_cantidad) * 100, 2),
        ]);
    }

    $total_productos = array_values(Arr::sort($ultimas_ventas, function (array $value) {
        return $value['resultado_final'];
    }));

        foreach ($total_productos as $key => $vent) {
            $f++;
            $sheet->setCellValueExplicit('A' . $f, $vent['sku'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('B' . $f, $vent['nombre']);
            $sheet->setCellValue('C' . $f, $vent['resultado_final']);
        }

        $spreadsheet->getActiveSheet()->getPageSetup()->setFitToWidth(0);
        foreach ($sheet->getColumnIterator() as $column) {
            $sheet->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
        }
        //alto de celdas
        foreach ($sheet->getRowIterator() as $row) {
            $sheet->getRowDimension($row->getRowIndex())->setRowHeight(20);
        }
        //guardando excel
        $writer = new Xlsx($spreadsheet);
        $writer->save('reportes/' . $filename);
        sleep(2);
        $url_save = public_path() . "/reportes/" . $filename;
        $name_file_header = "filename=" . $filename . "";
        $headers = [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => $name_file_header,
        ];


        //descargar excel
        try {

            $content = file_get_contents($url_save);
        } catch (Exception $e) {
            exit($e->getMessage());
        }

        header("Content-Disposition: attachment; filename=" . $filename);
        unlink($url_save);
        return $content;
    }
}
