<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\ImportacionDetalle;
use App\Models\Producto;
use App\Models\ProductoYuan;
use App\Models\TipoCambioYuan;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Arr;
use Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Traits\PaginationTrait;
class ReporteProductoVendidoController extends Controller
{
	use PaginationTrait;
    public function index()
    {

		$categorias = Categoria::orderBy('name', 'ASC')->get();
        $lista_categorias = [];
        foreach ($categorias as $value) {
            array_push($lista_categorias, [
                'value' => $value->id,
                'label' =>  $value->name,
            ]);
        }
			$query_total_productos = Producto::query()
			->select(
				'productos.nombre',
                'productos.origen',
                'productos.imagen',
                'productos.stock',
                'productos.id',
                'det.producto_id',
                DB::raw("sum(det.cantidad) as ventas_totales")
			)
			->join('venta_detalles as det', 'det.producto_id', '=', 'productos.id')
			->join('ventas as ve', 've.id', '=', 'det.venta_id')
			->with(['categorias' => function ($query) {
				$query->select(DB::raw("id,name"))->orderBy('name', 'ASC');
			}])
            ->when(Request::input('inicio'), function ($query) {
                $query->whereDate('ve.fecha_facturacion', '>=', Request::input('inicio'));
            })
            ->when(Request::input('fin'), function ($query) {
                $query->whereDate('ve.fecha_facturacion', '<=', Request::input('fin'));
            })
			->when(Request::input('buscar'), function ($query) {
				$query->where(DB::raw('lower(origen)'), 'LIKE', '%' . strtolower(Request::input('buscar')) . '%')
					->orWhere(DB::raw('lower(nombre)'), 'LIKE', '%' . strtolower(Request::input('buscar')) . '%');
			})
			->when(Request::input('categoria'), function ($query) {
				$query->whereHas('categorias', function ($query) {
					$query->whereIn('id', Request::input('categoria'));
				});
			})
            ->where('det.producto_validado', '=', 1)

            ->groupBy('productos.id')
            ->get();

        $total_cantidad = 0;
        $total_cantidad = array_sum(array_column($query_total_productos->toArray(), 'ventas_totales'));
        $ultimas_ventas = [];

        foreach ($query_total_productos as $vent) {
            //calculo
            $costo_aprox = 0;
            $ultimo_yang = 0;
            $ultimo_importacion = ImportacionDetalle::select('precio')->where('sku', $vent->origen)->latest()->first();
            if (!is_null($ultimo_importacion)) {

                $ultimo_precio = $ultimo_importacion->precio;
            } else {
                $ultimo_precio = 0;
            }
            $tipo_yuan = ProductoYuan::where('producto_id', '=', $vent->id)->latest()->first();
            if (!is_null($tipo_yuan)) {
                $tipo_cambio_yuan = TipoCambioYuan::findOrFail($tipo_yuan->tipo_cambio_yuan_id);
            }
            if (!is_null($tipo_yuan)) {

                $ultimo_yang = $tipo_cambio_yuan->valor;
                $costo_aprox = $ultimo_precio * 1.70 / $ultimo_yang;
            } else {
                $ultimo_yang = 0;
            }
			$l_cat=$vent->categorias->map(function ($item, int $key) {
				return $item->name;
			});
            array_push($ultimas_ventas, [
                "id" => $vent->id,
                "sku" => $vent->origen,
                "nombre" => $vent->nombre,
                "stock" => $vent->stock,
				"categorias" => !is_null($vent->categorias)?implode(", ",$l_cat->all()):'',
                "imagen" => $vent->imagen,
                'costo_aprox' => number_format($costo_aprox, 2, ','),
                "ventas_totales" => $vent->ventas_totales,
                "porcentaje" => round(($vent->ventas_totales / $total_cantidad) * 100, 2),
            ]);
        }
        $total_productos = array_values(Arr::sortDesc($ultimas_ventas, function (array $value) {
            return $value['porcentaje'];
        }));
		$datos = $this->paginate($total_productos, 100);
		$datos->withPath('/reportes-productos-vendidos')
		->withQueryString();

        return Inertia::render('Reporte/ProductosVendidos', [
            'total_cantidad' => $total_cantidad,
			'filtro' => Request::only(['buscar', 'categoria', 'inicio', 'fin']),
			'lista_categorias' => $lista_categorias,
            'total_productos' => $datos,

        ]);
    }

    public function exportProductoVentas()
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
        $filename = "PRODUCTOS_VENDIDOS_" . $fecha_inicio->format('d_m_Y') . "_AL_" . $fecha_fin->format('d_m_Y') . ".xlsx";

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
        $sheet->setCellValue('C' . (string)$f, "CATEGORIA");
        $sheet->setCellValue('D' . (string)$f, "STOCK");
        $sheet->setCellValue('E' . (string)$f, "COSTO APROXIMADO");
        $sheet->setCellValue('F' . (string)$f, "VENTAS TOTALES");
        $sheet->setCellValue('G' . (string)$f, "PORCENTAJE");

        $sheet->getStyle('A' . (string)3 . ':' . 'G' . (string)3)->getFont()->setBold(true);
        $sheet->getStyle('A' . (string)3 . ':' . 'G' . (string)3)->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A' . (string)3 . ':' . 'G' . (string)3)->getAlignment()->setVertical('center');

        //datos
		$query_total_productos = Producto::query()
		->select(
			'productos.nombre',
			'productos.origen',
			'productos.imagen',
			'productos.stock',
			'productos.id',
			'det.producto_id',
			DB::raw("sum(det.cantidad) as ventas_totales")
		)
		->join('venta_detalles as det', 'det.producto_id', '=', 'productos.id')
		->join('ventas as ve', 've.id', '=', 'det.venta_id')
		->with(['categorias' => function ($query) {
			$query->select(DB::raw("id,name"))->orderBy('name', 'ASC');
		}])
		->when(Request::input('inicio'), function ($query) {
			$query->whereDate('ve.fecha_facturacion', '>=', Request::input('inicio'));
		})
		->when(Request::input('fin'), function ($query) {
			$query->whereDate('ve.fecha_facturacion', '<=', Request::input('fin'));
		})
		->when(Request::input('categoria'), function ($query) {
			$query->whereHas('categorias', function ($query) {
				$query->whereIn('id', Request::input('categoria'));
			});
		})
		->where('det.producto_validado', '=', 1)

		->groupBy('productos.id')
		->get();

        $total_cantidad = 0;
        $total_cantidad = array_sum(array_column($query_total_productos->toArray(), 'ventas_totales'));
        $ultimas_ventas = [];

        foreach ($query_total_productos as $vent) {
            //calculo
            $costo_aprox = 0;
            $ultimo_yang = 0;
            $ultimo_importacion = ImportacionDetalle::select('precio')->where('sku', $vent->origen)->latest()->first();
            if (!is_null($ultimo_importacion)) {

                $ultimo_precio = $ultimo_importacion->precio;
            } else {
                $ultimo_precio = 0;
            }
            $tipo_yuan = ProductoYuan::where('producto_id', '=', $vent->id)->latest()->first();
            if (!is_null($tipo_yuan)) {
                $tipo_cambio_yuan = TipoCambioYuan::findOrFail($tipo_yuan->tipo_cambio_yuan_id);
            }
            if (!is_null($tipo_yuan)) {

                $ultimo_yang = $tipo_cambio_yuan->valor;
                $costo_aprox = $ultimo_precio * 1.70 / $ultimo_yang;
            } else {
                $ultimo_yang = 0;
            }
			$l_cat=$vent->categorias->map(function ($item, int $key) {
				return $item->name;
			});
            array_push($ultimas_ventas, [
                "id" => $vent->id,
                "sku" => $vent->origen,
                "nombre" => $vent->nombre,
				"categorias" => !is_null($vent->categorias)?implode(", ",$l_cat->all()):'',
                "stock" => $vent->stock,
                'costo_aprox' => round(($costo_aprox), 2),
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
            $sheet->setCellValue('C' . $f, $vent['categorias']);
            $sheet->setCellValue('D' . $f, $vent['stock']);
            $sheet->setCellValue('E' . $f,$vent['costo_aprox']);
            $sheet->setCellValue('F' . $f, $vent['ventas_totales']);
            $sheet->setCellValue('G' . $f, $vent['porcentaje']);
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
