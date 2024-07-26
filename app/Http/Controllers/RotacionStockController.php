<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
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
use App\Traits\PaginationTrait;

class RotacionStockController extends Controller
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
		$consulta_ventas = Producto::query()
			->select(
				'productos.nombre',
				'productos.origen',
				'productos.stock',
				'productos.stock_futuro',
				'productos.id',
				'det.producto_validado',
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
			->whereNotNull('det.venta_id')
			->where('det.producto_validado', '=', 1)
			->groupBy('productos.id')
			->get();

		$ultimas_ventas = [];

		$toDate = Carbon::parse(Request::input('fin'));
		$fromDate = Carbon::parse(Request::input('inicio'));

		$meses = $toDate->diffInMonths($fromDate);

		foreach ($consulta_ventas as $venta) {

				$ultima_compra_venta=DB::select("CALL sp_prod_vendido_comprado(?)",[$venta->id]);

			if ($meses <= 0) {
				$meses = 1;
			} else {
				$meses = $meses;
			}
			$rotacion_stock = round($venta->stock_futuro / ($venta->ventas_totales / $meses));
			$l_cat=$venta->categorias->map(function ($item, int $key) {
				return $item->name;
			});
			array_push($ultimas_ventas, [
				"origen" => $venta->origen,
				"nombre" => $venta->nombre,
				"nombre" => $venta->nombre,
				"categorias" => !is_null($venta->categorias)?implode(", ",$l_cat->all()):'',
				"ultima_compra" =>!empty($ultima_compra_venta) ? $ultima_compra_venta[0]->fecha_compra : '',
				"ultima_venta" => !empty($ultima_compra_venta) ? $ultima_compra_venta[0]->fecha_venta : '',
				"ventas_totales" => $venta->ventas_totales,
				"stock" => $venta->stock,
				"stock_futuro" => $venta->stock_futuro,
				"rotacion_stock" => $rotacion_stock,
			]);
		}

		$datos = $this->paginate($ultimas_ventas, 100);
		$datos->withPath('/rotacion-stock')
		->withQueryString();
		return Inertia::render('RotacionStock/Index', [
			'meses' => $meses,
			'lista_categorias' => $lista_categorias,
			'productos' => $datos,
			'filtro' => Request::only(['buscar', 'categoria', 'inicio', 'fin'])
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
		$sheet->setCellValue('C' . (string)$f, "CATEGORIA");
		$sheet->setCellValue('D' . (string)$f, "FECHA ULTIMA COMPRA");
		$sheet->setCellValue('E' . (string)$f, "FECHA ULTIMA VENTA");
		$sheet->setCellValue('F' . (string)$f, "VENTAS TOTALES");
		$sheet->setCellValue('G' . (string)$f, "STOCK");
		$sheet->setCellValue('H' . (string)$f, "STOCK FUTURO");
		$sheet->setCellValue('I' . (string)$f, "ROTACION DEL STOCK ");


		$sheet->getStyle('A' . (string)3 . ':' . 'I' . (string)3)->getFont()->setBold(true);
		//$sheet->getStyle('A' . (string)3 . ':' . 'H' . (string)3)->applyFromArray($styleArray);
		$sheet->getStyle('A' . (string)3 . ':' . 'I' . (string)3)->getAlignment()->setHorizontal('center');
		$sheet->getStyle('A' . (string)3 . ':' . 'I' . (string)3)->getAlignment()->setVertical('center');
		/*$sheet->getStyle('A' . (string)3 . ':' . 'H' . (string)3)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFB8CCE4');*/


		//datos

		$consulta_ventas = Producto::query()
		->select(
			'productos.nombre',
			'productos.origen',
			'productos.stock',
			'productos.stock_futuro',
			'productos.id',
			'det.producto_validado',
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
		->whereNotNull('det.venta_id')
		->where('det.producto_validado', '=', 1)
		->groupBy('productos.id')
		->get();

	$ultimas_ventas = [];

	$toDate = Carbon::parse(Request::input('fin'));
	$fromDate = Carbon::parse(Request::input('inicio'));

	$meses = $toDate->diffInMonths($fromDate);

	foreach ($consulta_ventas as $venta) {

			$ultima_compra_venta=DB::select("CALL sp_prod_vendido_comprado(?)",[$venta->id]);

		if ($meses <= 0) {
			$meses = 1;
		} else {
			$meses = $meses;
		}
		$rotacion_stock = round($venta->stock_futuro / ($venta->ventas_totales / $meses));
		$l_cat=$venta->categorias->map(function ($item, int $key) {
			return $item->name;
		});
		array_push($ultimas_ventas, [
			"origen" => $venta->origen,
			"nombre" => $venta->nombre,
			"categorias" => !is_null($venta->categorias)?implode(", ",$l_cat->all()):'',
			"ultima_compra" =>!empty($ultima_compra_venta) ? $ultima_compra_venta[0]->fecha_compra : '',
			"ultima_venta" => !empty($ultima_compra_venta) ? $ultima_compra_venta[0]->fecha_venta : '',
			"ventas_totales" => $venta->ventas_totales,
			"stock" => $venta->stock,
			"stock_futuro" => $venta->stock_futuro,
			"rotacion_stock" => $rotacion_stock,
		]);
	}


		/*$sorted = array_values(Arr::sort($ultimas_ventas, function (array $value) {
            return $value['rotacion_stock'];
        }));*/

		$sorted = array_values(Arr::sort($ultimas_ventas, function (array $value) {
			return $value['rotacion_stock'];
		}));

		foreach ($sorted as $key => $vent) {

			$f++;
			$sheet->setCellValueExplicit('A' . $f, $vent['origen'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
			$sheet->setCellValue('B' . $f, $vent['nombre']);
			$sheet->setCellValue('C' . $f, $vent['categorias']);
			$sheet->setCellValue('D' . $f, $vent['ultima_compra']);
			$sheet->setCellValue('E' . $f, $vent['ultima_venta']);
			$sheet->setCellValue('F' . $f, $vent['ventas_totales']);
			$sheet->setCellValue('G' . $f, $vent['stock']);
			$sheet->setCellValue('H' . $f, $vent['stock_futuro']);
			$sheet->setCellValue('I' . $f, $vent['rotacion_stock']);
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
