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

        $query_total_productos = DB::table('venta_detalles as det')
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
                //'prod.imagen',
                'prod.codigo_barra',
                'prod.stock',
                'prod.id',
                'det.producto_id',
                DB::raw("sum(det.cantidad) as ventas_totales")
            )
            ->groupBy('prod.id')
            ->get();

        $total_cantidad = 0;
        $total_cantidad = array_sum(array_column($query_total_productos->toArray(), 'ventas_totales'));
        $ultimas_ventas = [];

        foreach ($query_total_productos as $vent) {
          
            array_push($ultimas_ventas, [
                "id" => $vent->id,
                "sku" => $vent->origen,
                "nombre" => $vent->nombre,
                "codigo_barra" => $vent->codigo_barra,
                //"stock" => $vent->stock,
                "stock" => $vent->stock+ $vent->ventas_totales,
                //"imagen" => $vent->imagen,
                "ventas_totales" => $vent->ventas_totales,
                "total_productos" => round(($vent->ventas_totales / $total_cantidad) * 100, 2),
            ]);
        }
        $total_productos = array_values(Arr::sortDesc($ultimas_ventas, function (array $value) {
            return $value['total_productos'];
        }));

        return Inertia::render('Reporte/StockProductos', [
            'total_cantidad' => $total_cantidad,
            'total_productos' => $total_productos,

        ]);
    }

    public function exportXls()
    {

        $fecha_inicio = Carbon::parse(Request::input('inicio'));
        $fecha_fin = Carbon::parse(Request::input('fin'));

        $filename = "STOCK_PRODUCTOS_" . $fecha_inicio->format('d_m_Y'). ".xlsx";

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', "FECHA: ");
        $sheet->setCellValue('B1', $fecha_inicio->format('d/m/Y'));
        $sheet->getStyle('A' . (string)1 . ':' . 'C' . (string)1)->getFont()->setBold(true);
        $sheet->getStyle('A' . (string)1 . ':' . 'C' . (string)1)->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A' . (string)1 . ':' . 'C' . (string)1)->getAlignment()->setVertical('center');
        $f = 3;

        $sheet->setCellValue('A' . (string)$f, "SKU");
        $sheet->setCellValue('B' . (string)$f, "NOMBRE");
        $sheet->setCellValue('C' . (string)$f, "STOCK");
        $sheet->setCellValue('D' . (string)$f, "CODIGO BARRA");

        $sheet->getStyle('A' . (string)3 . ':' . 'D' . (string)3)->getFont()->setBold(true);
        $sheet->getStyle('A' . (string)3 . ':' . 'D' . (string)3)->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A' . (string)3 . ':' . 'D' . (string)3)->getAlignment()->setVertical('center');

        //datos
        $query_total_productos = DB::table('venta_detalles as det')
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
                //'prod.imagen',
                'prod.codigo_barra',
                'prod.stock',
                'prod.id',
                'det.producto_id',
                DB::raw("sum(det.cantidad) as ventas_totales")
            )
            ->groupBy('prod.id')
            ->get();

        $total_cantidad = 0;
        $total_cantidad = array_sum(array_column($query_total_productos->toArray(), 'ventas_totales'));
        $ultimas_ventas = [];

        foreach ($query_total_productos as $vent) {
          
            array_push($ultimas_ventas, [
                "id" => $vent->id,
                "sku" => $vent->origen,
                "nombre" => $vent->nombre,
                //"stock" => $vent->stock,
                "stock" => $vent->stock+ $vent->ventas_totales,
                "codigo_barra" => $vent->codigo_barra,
                //"imagen" => $vent->imagen,
                "ventas_totales" => $vent->ventas_totales,
                "total_productos" => round(($vent->ventas_totales / $total_cantidad) * 100, 2),
            ]);
        }
        $total_productos = array_values(Arr::sortDesc($ultimas_ventas, function (array $value) {
            return $value['total_productos'];
        }));

        foreach ($total_productos as $key => $vent) {
            $f++;
            $sheet->setCellValueExplicit('A' . $f, $vent['sku'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('B' . $f, $vent['nombre']);
            $sheet->setCellValue('C' . $f, $vent['stock']);
            $sheet->setCellValueExplicit('D' . $f, $vent['codigo_barra'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        }

        //$sheet->getStyle('A4:H4' . $sheet->getHighestRow())->getAlignment()->setVertical('center');
        //activando auto size
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
