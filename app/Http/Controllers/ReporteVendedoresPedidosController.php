<?php

namespace App\Http\Controllers;

use App\Models\ImportacionDetalle;
use App\Models\ProductoYuan;
use App\Models\TipoCambioYuan;
use App\Models\User;
use App\Models\Venta;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Arr;
use Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReporteVendedoresPedidosController extends Controller
{
    public function index()
    {

        $inicio = Request::input('inicio');
        $final = Request::input('fin');
		$query_vendedor=DB::table('users as us')
                    ->join('ventas as ve', 'us.id', '=', 've.vendedor_id')
                    ->when($inicio, function ($query, $inicio) {
                        $query->whereDate('ve.fecha_facturacion', '>=', $inicio);
                    })
                    ->when($final, function ($query, $final) {
                        $query->whereDate('ve.fecha_facturacion', '<=', $final)
						     ->where('ve.destino', '=', 'SALON');
                    })
		->select('us.id')
		->groupBy('us.id')
		->get()->toArray();


        if (!is_null($query_vendedor)) {
            $id_vendedores = array_column($query_vendedor, 'id');
            $ultimas_ventas = [];
            foreach ($id_vendedores as $key => $vend) {
                $query_total_ventas = DB::table('users as us')
                    ->join('ventas as ve', 'us.id', '=', 've.vendedor_id')
                    ->when($inicio, function ($query, $inicio) {
                        $query->whereDate('ve.fecha_facturacion', '>=', $inicio);
                    })
                    ->when($final, function ($query, $final) {
                        $query->whereDate('ve.fecha_facturacion', '<=', $final);
                    })
                    ->where('ve.facturado', '=', '1')
                    ->where('ve.destino', '=', 'SALON')
                    ->where('us.id', '=', $vend)
                    ->select(
                        'us.name',
                        've.vendedor_id',
                        've.id',
                        DB::raw("sum(ve.total) as ventas_totales,COUNT(ve.id) AS pedidos")
                    )
                    ->groupBy('us.id')
                    ->orderBy('pedidos', 'ASC')
                    ->first();

                if (!is_null($query_total_ventas)) {
                    array_push($ultimas_ventas, [
                        'nombre' => $query_total_ventas->name,
                        'pedidos' => number_format($query_total_ventas->pedidos, 2, ','),
                        "total" => round(($query_total_ventas->ventas_totales), 2),
                    ]);
                } else {
                    $vendor = User::where('id', '=', $vend)->first();
                    array_push($ultimas_ventas, [
                        'nombre' => $vendor->name,
                        'pedidos' => 0,
                        "total" => 0,

                    ]);
                }
            }
        }
        $total_productos = array_values(Arr::sortDesc($ultimas_ventas, function (array $value) {
            return $value['total'];
        }));

        return Inertia::render('Reporte/VendedoresPedidos', [
            'total_ventas' => $total_productos ?? []
        ]);
    }

    public function exportVendedoresPedidos()
    {

        $inicio = Carbon::parse(Request::input('inicio'));
        $final = Carbon::parse(Request::input('fin'));

        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '00000000'],
                ],
            ]

        ];
        $filename = "VENDEDORES_PEDIDOS_" . $inicio->format('d_m_Y') . "_AL_" . $final->format('d_m_Y') . ".xlsx";

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', "FECHA: ");
        $sheet->setCellValue('B1', $inicio->format('d/m/Y') . " AL " . $final->format('d/m/Y'));
        $sheet->getStyle('A' . (string)1 . ':' . 'B' . (string)1)->getFont()->setBold(true);
        $sheet->getStyle('A' . (string)1 . ':' . 'B' . (string)1)->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A' . (string)1 . ':' . 'B' . (string)1)->getAlignment()->setVertical('center');
        $f = 3;

        $sheet->setCellValue('A' . (string)$f, "VENDEDOR");
        $sheet->setCellValue('B' . (string)$f, "CANTIDAD PEDIDOS");
        $sheet->setCellValue('C' . (string)$f, "TOTAL");

        $sheet->getStyle('A' . (string)3 . ':' . 'C' . (string)3)->getFont()->setBold(true);
        $sheet->getStyle('A' . (string)3 . ':' . 'C' . (string)3)->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A' . (string)3 . ':' . 'C' . (string)3)->getAlignment()->setVertical('center');

        //datos
		$query_vendedor=DB::table('users as us')
                    ->join('ventas as ve', 'us.id', '=', 've.vendedor_id')
                    ->when($inicio, function ($query, $inicio) {
                        $query->whereDate('ve.fecha_facturacion', '>=', $inicio);
                    })
                    ->when($final, function ($query, $final) {
                        $query->whereDate('ve.fecha_facturacion', '<=', $final)
						     ->where('ve.destino', '=', 'SALON');
                    })
		->select('us.id')
		->groupBy('us.id')
		->get()->toArray();


        if (!is_null($query_vendedor)) {
            $id_vendedores = array_column($query_vendedor, 'id');
            $ultimas_ventas = [];
            foreach ($id_vendedores as $key => $vend) {
                $query_total_ventas = DB::table('users as us')
                    ->join('ventas as ve', 'us.id', '=', 've.vendedor_id')
                    ->when($inicio, function ($query, $inicio) {
                        $query->whereDate('ve.fecha_facturacion', '>=', $inicio);
                    })
                    ->when($final, function ($query, $final) {
                        $query->whereDate('ve.fecha_facturacion', '<=', $final);
                    })
                    ->where('ve.facturado', '=', '1')
                    ->where('ve.destino', '=', 'SALON')
                    ->where('us.id', '=', $vend)
                    ->select(
                        'us.name',
                        've.vendedor_id',
                        've.id',
                        DB::raw("sum(ve.total) as ventas_totales,COUNT(ve.id) AS pedidos")
                    )
                    ->groupBy('us.id')
                    ->orderBy('pedidos', 'ASC')
                    ->first();

                if (!is_null($query_total_ventas)) {
                    array_push($ultimas_ventas, [
                        'nombre' => $query_total_ventas->name,
                        'pedidos' =>  round(($query_total_ventas->pedidos), 2),
                        "total" => round(($query_total_ventas->ventas_totales), 2),
                    ]);
                } else {
                    $vendor = User::where('id', '=', $vend)->first();
                    array_push($ultimas_ventas, [
                        'nombre' => $vendor->name,
                        'pedidos' => 0,
                        "total" => 0,

                    ]);
                }
            }
        }
        $total_productos = array_values(Arr::sortDesc($ultimas_ventas, function (array $value) {
            return $value['total'];
        }));

        foreach ($total_productos as $key => $vent) {
            $f++;
            $sheet->setCellValue('A' . $f, $vent['nombre']);
            $sheet->setCellValue('B' . $f, $vent['pedidos']);
            $sheet->setCellValue('C' . $f, $vent['total']);
        }

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
