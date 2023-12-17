<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\VentaDetalle;
use Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Request;
use Carbon\Carbon;

class RotacionStockController extends Controller
{

    public function index()
    {


        $consulta_ventas = DB::table('venta_detalles as det')
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
                'prod.stock',
                'prod.stock_futuro',
                'det.cantidad',
                'prod.id',
                'det.producto_id',
                DB::raw("sum(det.cantidad) as ventas_totales")
            )

            ->groupBy('prod.id')
            ->get();


        $ultimas_ventas = [];

        $toDate = Carbon::parse(Request::input('fin'));
        $fromDate = Carbon::parse(Request::input('inicio'));

        $days = $toDate->diffInDays($fromDate);
        $meses = $toDate->diffInMonths($fromDate);
        $years = $toDate->diffInYears($fromDate);



        foreach ($consulta_ventas as $key => $venta) {


            $ultima_venta = VentaDetalle::where('producto_id', $venta->id)
                ->where('producto_validado', '=', 1)
                ->select(DB::raw("DATE_FORMAT(created_at ,'%d/%m/%Y') AS fecha"))
                ->latest()->first();
            $ultima_compra = DB::table('importaciones as imp')
                ->join('importaciones_detalles as det', 'imp.id', '=', 'det.importacion_id')
                ->where('imp.estado', '=', 'Arribado')
                ->select('imp.id', 'imp.estado', 'imp.created_at', 'det.sku', DB::raw("DATE_FORMAT(fecha_arribado ,'%d/%m/%Y') AS fecha"))
                ->where('det.sku', '=', $venta->origen)->latest('fecha_arribado')->first();
            if ($meses <= 0) {
                $meses = 1;
            } else {
                $meses = $meses;
            }
            $rotacion_stock = round($venta->stock_futuro / ($venta->ventas_totales / $meses));
            array_push($ultimas_ventas, [
                "origen" => $venta->origen,
                "nombre" => $venta->nombre,
                "ultima_compra" => $ultima_compra ? $ultima_compra->fecha : '',
                "ultima_venta" => $ultima_venta ? $ultima_venta->fecha : '',
                "ventas_totales" => $venta->ventas_totales,
                "stock" => $venta->stock,
                "stock_futuro" => $venta->stock_futuro,
                "rotacion_stock" => $rotacion_stock,
            ]);
        }

        $consulta_productos = Producto::select(
            'id',
            'origen',
            'nombre',
            'stock',
            'stock_futuro',

        )
        ->get();

        $listado_final=[];

        foreach ($consulta_productos as $product) {
            $stck=0;
            $ulti_venta='';
            $ulti_compra='';
            $ventas_totales=0;
            foreach ($ultimas_ventas as $key =>$ult_venta) {

                if($ult_venta['origen']==$product->origen){

                    $stck=$ult_venta['rotacion_stock'];
                    $ulti_venta=$ult_venta['ultima_venta'];
                    $ulti_compra=$ult_venta['ultima_compra'];
                    $ventas_totales=$ult_venta['ventas_totales'];
                    continue;
                }

            }
            array_push($listado_final, [
                "origen" => $product->origen,
                "nombre" => $product->nombre,
                "stock" => $product->stock,
                "stock_futuro" => $product->stock_futuro,
                "ultima_compra" => $ulti_compra,
                "ultima_venta" => $ulti_venta,
                "ventas_totales" => $ventas_totales,
                "rotacion_stock" =>  $stck
            ]);

        }


        return Inertia::render('RotacionStock/Index', [
            'meses' => $meses,
            'productos' => $listado_final
        ]);
    }


    public function exportProductoVentas()
    {

        $fecha_inicio = Carbon::parse(Request::input('inicio'));
        $fecha_fin = Carbon::parse(Request::input('fin'));

        $meses = $fecha_fin->diffInMonths($fecha_inicio);
        //$fecha_inicio->format('d/m/Y')
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '00000000'],
                ],
            ]

        ];
        $filename = "ROTACION_STOCK_" . $fecha_inicio->format('d_m_Y') . "_AL_" . $fecha_fin->format('d_m_Y') . ".xlsx";

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('B1', $meses . " MESES");
        $sheet->setCellValue('C1', "FECHA: ");
        $sheet->setCellValue('D1', $fecha_inicio->format('d/m/Y') . " AL " . $fecha_fin->format('d/m/Y'));
        $sheet->getStyle('B' . (string)1 . ':' . 'D' . (string)1)->getFont()->setBold(true);
        $sheet->getStyle('B' . (string)1 . ':' . 'D' . (string)1)->getAlignment()->setHorizontal('center');
        $sheet->getStyle('B' . (string)1 . ':' . 'D' . (string)1)->getAlignment()->setVertical('center');
        $f = 3;

        $sheet->setCellValue('A' . (string)$f, "ORIGEN");
        $sheet->setCellValue('B' . (string)$f, "NOMBRE");
        $sheet->setCellValue('C' . (string)$f, "FECHA ULTIMA COMPRA");
        $sheet->setCellValue('D' . (string)$f, "FECHA ULTIMA VENTA");
        $sheet->setCellValue('E' . (string)$f, "VENTAS TOTALES");
        $sheet->setCellValue('F' . (string)$f, "STOCK");
        $sheet->setCellValue('G' . (string)$f, "STOCK FUTURO");
        $sheet->setCellValue('H' . (string)$f, "ROTACION DEL STOCK ");


        $sheet->getStyle('A' . (string)3 . ':' . 'H' . (string)3)->getFont()->setBold(true);
        //$sheet->getStyle('A' . (string)3 . ':' . 'H' . (string)3)->applyFromArray($styleArray);
        $sheet->getStyle('A' . (string)3 . ':' . 'H' . (string)3)->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A' . (string)3 . ':' . 'H' . (string)3)->getAlignment()->setVertical('center');
        /*$sheet->getStyle('A' . (string)3 . ':' . 'H' . (string)3)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFB8CCE4');*/


        //datos

        $consulta_ventas = DB::table('venta_detalles as det')
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
                'prod.stock',
                'prod.stock_futuro',
                'det.cantidad',
                'prod.id',
                'det.producto_id',
                DB::raw("sum(det.cantidad) as ventas_totales")
            )

            ->groupBy('prod.id')
            ->get();


        $ultimas_ventas = [];



        foreach ($consulta_ventas as $key => $venta) {


            $ultima_venta = VentaDetalle::where('producto_id', $venta->id)
                ->where('producto_validado', '=', 1)
                ->select(DB::raw("DATE_FORMAT(created_at ,'%d/%m/%Y') AS fecha"))
                ->latest()->first();
            $ultima_compra = DB::table('importaciones as imp')
                ->join('importaciones_detalles as det', 'imp.id', '=', 'det.importacion_id')
                ->where('imp.estado', '=', 'Arribado')
                ->select('imp.id', 'imp.estado', 'imp.created_at', 'det.sku', DB::raw("DATE_FORMAT(fecha_arribado ,'%d/%m/%Y') AS fecha"))
                ->where('det.sku', '=', $venta->origen)->latest('fecha_arribado')->first();
            if ($meses <= 0) {
                $meses = 1;
            } else {
                $meses = $meses;
            }
            $rotacion_stock = round($venta->stock_futuro / ($venta->ventas_totales / $meses));
            array_push($ultimas_ventas, [
                "origen" => $venta->origen,
                "nombre" => $venta->nombre,
                "ultima_compra" => $ultima_compra ? $ultima_compra->fecha : '',
                "ultima_venta" => $ultima_venta ? $ultima_venta->fecha : '',
                "ventas_totales" => $venta->ventas_totales,
                "stock" => $venta->stock,
                "stock_futuro" => $venta->stock_futuro,
                "rotacion_stock" => $rotacion_stock,
            ]);
        }
        $consulta_productos = Producto::select(
            'id',
            'origen',
            'nombre',
            'stock',
            'stock_futuro',

        )
        ->get();

        $listado_final=[];

        foreach ($consulta_productos as $product) {
            $stck=0;
            $ulti_venta='';
            $ulti_compra='';
            $ventas_totales=0;
            foreach ($ultimas_ventas as $key =>$ult_venta) {

                if($ult_venta['origen']==$product->origen){

                    $stck=$ult_venta['rotacion_stock'];
                    $ulti_venta=$ult_venta['ultima_venta'];
                    $ulti_compra=$ult_venta['ultima_compra'];
                    $ventas_totales=$ult_venta['ventas_totales'];
                    continue;
                }

            }
            array_push($listado_final, [
                "origen" => $product->origen,
                "nombre" => $product->nombre,
                "stock" => $product->stock,
                "stock_futuro" => $product->stock_futuro,
                "ultima_compra" => $ulti_compra,
                "ultima_venta" => $ulti_venta,
                "ventas_totales" => $ventas_totales,
                "rotacion_stock" =>  $stck
            ]);

        }

        /*$sorted = array_values(Arr::sort($ultimas_ventas, function (array $value) {
            return $value['rotacion_stock'];
        }));*/

        $sorted = array_values(Arr::sort($listado_final, function (array $value) {
            return $value['rotacion_stock'];
        }));

        foreach ($sorted as $key => $vent) {

            $f++;
            $sheet->setCellValueExplicit('A' . $f, $vent['origen'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('B' . $f, $vent['nombre']);
            $sheet->setCellValue('C' . $f, $vent['ultima_compra']);
            $sheet->setCellValue('D' . $f, $vent['ultima_venta']);
            $sheet->setCellValue('E' . $f, $vent['ventas_totales']);
            $sheet->setCellValue('F' . $f, $vent['stock']);
            $sheet->setCellValue('G' . $f, $vent['stock_futuro']);
            $sheet->setCellValue('H' . $f, $vent['rotacion_stock']);
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
