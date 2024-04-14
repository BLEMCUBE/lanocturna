<?php

namespace App\Http\Controllers;

use App\Models\RmaStock;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Arr;
use Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReporteProductoRmaController extends Controller
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
            ->where('ve.tipo', '=', "RMA")
            ->select(
                'prod.nombre',
                'prod.origen',
                'prod.imagen',
                'prod.id',
                'det.producto_id',
                DB::raw("sum(det.cantidad) as ventas_totales")
            )
            ->groupBy('prod.id')
            ->get();

        $total_cantidad = 0;
        foreach ($query_total_productos as $key => $value) {
            $total_cantidad = $total_cantidad + $value->ventas_totales;
        }

        $ultimas_ventas = [];

        foreach ($query_total_productos as $vent) {

            array_push($ultimas_ventas, [
                "sku" => $vent->origen,
                "nombre" => $vent->nombre,
                "imagen" => $vent->imagen,
                "ventas_totales" => $vent->ventas_totales,
                "porcentaje" => round(($vent->ventas_totales / $total_cantidad) * 100, 2),
            ]);
        }
        $total_productos = array_values(Arr::sortDesc($ultimas_ventas, function (array $value) {
            return $value['porcentaje'];
        }));

        return Inertia::render('Reporte/ProductosRma', [
            'total_cantidad' => $total_cantidad,
            'total_productos' => $total_productos,

        ]);
    }

    public function exportProductoRma()
    {

        $fecha_inicio = Carbon::parse(Request::input('inicio'));
        $fecha_fin = Carbon::parse(Request::input('fin'));
        $meses = $fecha_fin->diffInMonths($fecha_inicio);

        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '00000000'],
                ],
            ]

        ];
        $filename = "PRODUCTOS_RMA_" . $fecha_inicio->format('d_m_Y') . "_AL_" . $fecha_fin->format('d_m_Y') . ".xlsx";

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('B1', "FECHA: ");
        $sheet->setCellValue('C1', $fecha_inicio->format('d/m/Y') . " AL " . $fecha_fin->format('d/m/Y'));
        $sheet->getStyle('B' . (string)1 . ':' . 'C' . (string)1)->getFont()->setBold(true);
        $sheet->getStyle('B' . (string)1 . ':' . 'C' . (string)1)->getAlignment()->setHorizontal('center');
        $sheet->getStyle('B' . (string)1 . ':' . 'C' . (string)1)->getAlignment()->setVertical('center');
        $f = 3;

        $sheet->setCellValue('A' . (string)$f, "SKU");
        $sheet->setCellValue('B' . (string)$f, "NOMBRE");
        $sheet->setCellValue('C' . (string)$f, "CANTIDAD");
        $sheet->setCellValue('D' . (string)$f, "PORCENTAJE");



        $sheet->getStyle('A' . (string)3 . ':' . 'D' . (string)3)->getFont()->setBold(true);
        //$sheet->getStyle('A' . (string)3 . ':' . 'H' . (string)3)->applyFromArray($styleArray);
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
            ->where('ve.tipo', '=', "RMA")
            ->select(
                'prod.nombre',
                'prod.origen',
                'prod.id',
                'det.producto_id',
                DB::raw("sum(det.cantidad) as ventas_totales")
            )
            ->groupBy('prod.id')
            ->get();


        //$sum_tol=[]
        $total_cantidad = 0;
        foreach ($query_total_productos as $key => $value) {
            $total_cantidad = $total_cantidad + $value->ventas_totales;
        }

        $ultimas_ventas = [];

        foreach ($query_total_productos as $vent) {

            array_push($ultimas_ventas, [
                "sku" => $vent->origen,
                "nombre" => $vent->nombre,
                "ventas_totales" => $vent->ventas_totales,
                "porcentaje" => round(($vent->ventas_totales / $total_cantidad) * 100, 2),
            ]);
        }
        $total_productos = array_values(Arr::sortDesc($ultimas_ventas, function (array $value) {
            return $value['porcentaje'];
        }));

        foreach ($total_productos as $key => $vent) {

            $f++;
            $sheet->setCellValueExplicit('A' . $f, $vent['sku'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('B' . $f, $vent['nombre']);
            $sheet->setCellValue('C' . $f, $vent['ventas_totales']);
            $sheet->setCellValue('D' . $f, $vent['porcentaje']);
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

    public function exportStockRma($completo)
    {

        $tipo = $completo == "SI" ? "PRODUCTOS COMPLETO" : "PRODUCTOS PARCIALES";
        //$filename = "STOCK_RMA_" . $fecha_inicio->format('d_m_Y') . "_AL_" . $fecha_fin->format('d_m_Y') . ".xlsx";
        $filename = "STOCK RMA " . $tipo . ".xlsx";

        $styleArrayBody = [
            'font' => [
                'size' => 10,

            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrapText' => true
            ],
            /*'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '00000000'],
                ],
            ],*/

        ];


        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();


        $sheet->getColumnDimension('A')->setWidth(13);
        $sheet->getColumnDimension('B')->setWidth(40);
        $sheet->getColumnDimension('C')->setWidth(35);
        $sheet->getColumnDimension('D')->setWidth(50);
        $sheet->getColumnDimension('E')->setWidth(10);

        $sheet->mergeCells('A1:E1');
        $sheet->setCellValue('A1', "STOCK RMA DE " . $tipo);
        $sheet->getStyle('A' . (string)1 . ':' . 'C' . (string)1)->getFont()->setBold(true);
        $sheet->getStyle('A' . (string)1 . ':' . 'C' . (string)1)->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A' . (string)1 . ':' . 'C' . (string)1)->getAlignment()->setVertical('center');

        $f = 3;
        $sheet->setCellValue('A' . (string)$f, "SKU");
        $sheet->setCellValue('B' . (string)$f, "PRODUCTO");
        $sheet->setCellValue('C' . (string)$f, "DEFECTOS");
        $sheet->setCellValue('D' . (string)$f, "OBSERVACIONES");
        $sheet->setCellValue('E' . (string)$f, "CANTIDAD");

        $sheet->getStyle('A' . (string)3 . ':' . 'E' . (string)3)->getFont()->setBold(true);
        $sheet->getStyle('A' . (string)3 . ':' . 'E' . (string)3)->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A' . (string)3 . ':' . 'E' . (string)3)->getAlignment()->setVertical('center');


        $query_depositos = RmaStock::with(['producto' => function ($query) {
            $query->select('id', 'origen', 'nombre');
        }])->with(['rma' => function ($query) {
            $query->select('id', 'defecto', 'observaciones');
        }])->select('*')->orderBy('producto_completo', 'DESC')
            ->where('producto_completo', $completo)->get();


        foreach ($query_depositos as $key => $vent) {

            $f++;
            $sheet->setCellValueExplicit('A' . $f, $vent['sku'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('B' . $f, $vent['producto']['nombre']);
            $sheet->setCellValue('C' . $f, $vent['rma']['defecto']??"");
            $sheet->setCellValue('D' . $f, $vent['rma']['observaciones']??"");
            $sheet->setCellValue('E' . $f, $vent['cantidad_total']);
        }

        $last_row = $sheet->getHighestRow();
        $sheet->getStyle('A4' . ':' . 'E' . (string)$last_row)->applyFromArray($styleArrayBody);

        $spreadsheet->getActiveSheet()->getPageSetup()->setFitToWidth(1);

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
