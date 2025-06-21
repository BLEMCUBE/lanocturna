<?php

namespace App\Http\Controllers;

use App\Exports\ProductosExport;
use App\Exports\ProductoVentaExport;
use App\Http\Requests\ProductoImportStockRequest;
use App\Http\Requests\ProductoMasivoStoreRequest;
use App\Http\Requests\ProductoStoreRequest;
use App\Http\Requests\ProductoUpdateRequest;
use App\Imports\ProductoMasivoImport;
use App\Imports\ProductoStockImport;
use App\Models\Categoria;
use Illuminate\Validation\ValidationException;
use App\Models\ImportacionDetalle;
use App\Models\Producto;
use App\Models\ProductoYuan;
use App\Models\TipoCambioYuan;
use App\Models\VentaDetalle;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Exception;
use Illuminate\Support\Facades\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


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
		$categorias = Categoria::orderBy('name', 'ASC')->get();
		$lista_categorias = [];
		foreach ($categorias as $value) {
			array_push($lista_categorias, [
				'value' => $value->id,
				'label' =>  $value->name,
			]);
		}

		$productos_query = Producto::query()->select(
			'id',
			'origen',
			'nombre',
			'aduana',
			'codigo_barra',
			'imagen',
			'stock',
			'stock_minimo',
			'stock_futuro',
			'en_camino',
			'arribado',
			'created_at',
			DB::raw("DATE_FORMAT(created_at,'%d/%m/%y  %H:%i:%s') AS fecha")
		)
			->with(['categorias' => function ($query) {
				$query->select(DB::raw("id,name"))->orderBy('name', 'ASC');
			}])
			->when(Request::input('buscar'), function ($query) {
				$query->where(DB::raw('lower(origen)'), 'LIKE', '%' . strtolower(Request::input('buscar')) . '%')
					->orWhere(DB::raw('lower(nombre)'), 'LIKE', '%' . strtolower(Request::input('buscar')) . '%');
			})
			->when(Request::input('categoria'), function ($query) {
				$query->whereHas('categorias', function ($query) {
					$query->whereIn('id', Request::input('categoria'));
				});
			})

			->orderBy('nombre', 'ASC')
			->paginate(100)->withQueryString();


		return Inertia::render('Producto/Index', [
			'lista_categorias' => $lista_categorias,
			'productos' => $productos_query,
			'filtro' => Request::only(['buscar', 'categoria'])
		]);
	}
	public function ajusteStock()
	{
		return Inertia::render('Producto/AjustarStock');
	}

	public function create()
	{
		$categorias = Categoria::get();
		$lista_categorias = [];
		foreach ($categorias as $value) {
			array_push($lista_categorias, [
				'value' => $value->id,
				'label' =>  $value->name,
			]);
		}
		return Inertia::render(
			'Producto/Create',
			[
				'lista_categorias' => $lista_categorias
			]
		);
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

		if (!empty($request->categorias)) {
			$producto->categorias()->sync($request->categorias);
		}
	}

	public function edit($id)
	{
		$categorias = Categoria::orderBy('name', 'ASC')->get();
		$lista_categorias = [];
		foreach ($categorias as $value) {
			array_push($lista_categorias, [
				'value' => $value->id,
				'label' =>  $value->name,
			]);
		}
		$producto = Producto::with(['categorias' => function ($query) {
			$query->select(DB::raw("id,name"))->orderBy('name', 'ASC');
		}])->findOrFail($id);
		return Inertia::render('Producto/Edit', [
			'lista_categorias' => $lista_categorias,
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

		if (!empty($request->categorias)) {
			$producto->categorias()->sync($request->categorias);
		} else {
			$producto->categorias()->detach();
		}
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
		}])->with(['categorias' => function ($query) {
			$query->select(DB::raw("id,name"))->orderBy('name', 'ASC');
		}])->select(DB::raw("productos.*"))
			->orderBy('id', 'ASC')->findOrFail($id);


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
				'det.costo_real',
				'det.valor_total',
				'det.cbm_bulto',
				'det.cbm_total',
				'det.importacion_id',
				DB::raw("DATE_FORMAT(imp.fecha_arribado ,'%d/%m/%Y') AS fecha_arribado")
			)->where('prod.id', '=', $id)
			->orderBy('imp.fecha_arribado', 'DESC')->get();

		$productoEnCamino = DB::table('importaciones as imp')
			->join('importaciones_detalles as det', 'imp.id', '=', 'det.importacion_id')
			->join('productos as prod', 'prod.origen', '=', 'det.sku')
			->select(
				'imp.nro_carpeta',
				'imp.estado',
				'det.cantidad_total',
				'det.importacion_id',
				DB::raw("DATE_FORMAT(imp.fecha_arribado ,'%d/%m/%Y') AS fecha_arribado")
			)
			->where('prod.id', '=', $id)
			->where('imp.estado', '=', 'En camino')
			->orderBy('imp.nro_carpeta', 'DESC')->get();

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

		$tipo_yuan = ProductoYuan::where('producto_id', '=', $producto->id)->latest()->first();
		if (!is_null($tipo_yuan)) {
			$tipo_cambio_yuan = TipoCambioYuan::findOrFail($tipo_yuan->tipo_cambio_yuan_id);
		}


		$ultimo_importacion = DB::table('importaciones_detalles as det')
			->join('importaciones as imp', 'imp.id', '=', 'det.importacion_id')
			->select(
				'imp.fecha_arribado',
				'imp.estado',
				'det.precio',
				'det.sku',
				'det.costo_real',
			)
			->where('imp.estado', '=', 'Arribado')
			->where('det.sku', $producto->origen)
			->orderBy('imp.fecha_arribado', 'DESC')
			->limit(1)
			->first();

		$costo_aprox = 0;
		$ultimo_yang = 0;
		$costo_real = 0;

		if (!is_null($ultimo_importacion)) {

			$ultimo_precio = $ultimo_importacion->precio;
		} else {
			$ultimo_precio = 0;
		}
		if (!is_null($tipo_yuan)) {

			$ultimo_yang = $tipo_cambio_yuan->valor;
			$costo_aprox = $ultimo_precio * 1.70 / $ultimo_yang;
			$costo_real = $ultimo_importacion ? $ultimo_importacion->costo_real : 0;
		} else {
			$ultimo_yang = 0;
			$costo_real = 0;
		}


		return Inertia::render('Producto/Show', [
			'producto' => $producto,
			'productoImportacion' => $productoImportacion,
			'productoEnCamino' => $productoEnCamino,
			'cantidad' => $cantidad,
			'costo_aprox' => number_format($costo_aprox, 2, ','),
			'costo_real' => number_format($costo_real, 2, ','),
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
		$producto->categorias()->detach();
		$producto->delete();
	}

	public function exportExcel()
	{

		$productos = Producto::query()->select(
			'id',
			'origen',
			'nombre',
			'aduana',
			'codigo_barra',
			'imagen',
			'stock',
			'stock_minimo',
			'stock_futuro',
			'en_camino',
			'arribado'
		)
			->with(['categorias' => function ($query) {
				$query->select(DB::raw("id,name"))->orderBy('name', 'ASC');
			}])
			->when(Request::input('categoria'), function ($query) {
				$query->whereHas('categorias', function ($query) {
					$query->whereIn('id', Request::input('categoria'));
				});
			})
			->orderBy('nombre', 'ASC')
			->get();

		$styleArray = [
			'borders' => [
				'allBorders' => [
					'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
					'color' => ['argb' => '00000000'],
				],
			]

		];
		$filename = "productos.xlsx";
		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		$f = 1;
		$foto = Request::input('foto');
		if ($foto == "1") {
			$sheet->setCellValue('A' . (string)$f, "imagen");
		}
		$sheet->setCellValue('B' . (string)$f, "origen");
		$sheet->setCellValue('C' . (string)$f, "nombre");
		$sheet->setCellValue('D' . (string)$f, "categoria");
		$sheet->setCellValue('E' . (string)$f, "aduana");
		$sheet->setCellValue('F' . (string)$f, "codigo_barra");
		$sheet->setCellValue('G' . (string)$f, "stock");
		$sheet->setCellValue('H' . (string)$f, "stock_minimo");
		$sheet->setCellValue('I' . (string)$f, "stock_futuro");
		$sheet->setCellValue('J' . (string)$f, "arribado");
		$sheet->setCellValue('K' . (string)$f, "en_camino");


		$sheet->getStyle('A' . (string)1 . ':' . 'K' . (string)1)->getFont()->setBold(true);
		$sheet->getStyle('A' . (string)1 . ':' . 'K' . (string)1)->getAlignment()->setHorizontal('center');
		$sheet->getStyle('A' . (string)1 . ':' . 'K' . (string)1)->getAlignment()->setVertical('center');

		foreach ($productos as $key => $vent) {

			$f++;
			if ($foto == "1") {
				$drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
				$url_save = public_path() . $vent['imagen'];
				if (file_exists($url_save)) {
					$drawing->setPath($url_save);
					$drawing->setName($vent['nombre']);
					$drawing->setDescription($vent['nombre']);
					$drawing->setCoordinates('A' . $f);
					$drawing->setOffsetX(18);
					$drawing->setOffsetY(7);
					$drawing->setHeight(36);
					$drawing->setWorksheet($spreadsheet->getActiveSheet());
				} else {
					$drawing->setPath(public_path() . '/images/productos/sin_foto.png');
					$drawing->setName($vent['nombre']);
					$drawing->setDescription($vent['nombre']);
					$drawing->setCoordinates('A' . $f);
					$drawing->setHeight(36);
					$drawing->setOffsetX(18);
					$drawing->setOffsetY(7);
					$drawing->setWorksheet($spreadsheet->getActiveSheet());
				}
			}
			$sheet->setCellValueExplicit('B' . $f, $vent['origen'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
			$sheet->setCellValue('C' . $f, $vent['nombre']);
			$l_cat = $vent->categorias->map(function ($item, int $key) {
				return $item->name;
			});
			$sheet->setCellValue('D' . $f, !is_null($vent->categorias) ? implode(", ", $l_cat->all()) : '',);
			$sheet->setCellValue('E' . $f, $vent['aduana']);
			$sheet->setCellValue('F' . $f, $vent['codigo_barra']);
			$sheet->setCellValue('G' . $f, $vent['stock']);
			$sheet->setCellValue('H' . $f, $vent['stock_minimo']);
			$sheet->setCellValue('I' . $f, $vent['stock_futuro']);
			$sheet->setCellValue('J' . $f, $vent['arribado']);
			$sheet->setCellValue('K' . $f, $vent['en_camino']);

			$sheet->getStyle('A' . (string)$f . ':' . 'K' . (string)$f)->getAlignment()->setVertical('center');
		}
		$spreadsheet->getActiveSheet()->getPageSetup()->setFitToWidth(0);
		foreach ($sheet->getColumnIterator() as $column) {
			$sheet->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
		}
		//alto de celdas
		foreach ($sheet->getRowIterator() as $row) {
			$sheet->getRowDimension($row->getRowIndex())->setRowHeight(36);
		}
		//guardando excel
		$writer = new Xlsx($spreadsheet);
		$writer->save('reportes/' . $filename);
		sleep(2);
		$url_save = public_path() . "/reportes/" . $filename;
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

	public function exportExce2()
	{

		$productos = Producto::query()->select(
			'id',
			'origen',
			'nombre',
			'aduana',
			'codigo_barra',
			'imagen',
			'stock',
			'stock_minimo',
			'stock_futuro',
			'en_camino',
			'arribado'
		)
			->with(['categorias' => function ($query) {
				$query->select(DB::raw("id,name"))->orderBy('name', 'ASC');
			}])
			->when(Request::input('categoria'), function ($query) {
				$query->whereHas('categorias', function ($query) {
					$query->whereIn('id', Request::input('categoria'));
				});
			})

			->orderBy('nombre', 'ASC')
			->get();
		return Excel::download(new ProductosExport($productos), 'Productos.xlsx');
	}
	public function vistaImportar()
	{
		return Inertia::render('Producto/Importar');
	}
	public function importarStock(ProductoImportStockRequest $request)
	{
		$file = $request->file('archivo');
		$no_existe = [];
		$filas = Excel::toArray([], $file);
		$filas_a = array_slice($filas[0], 1);
		$n_fila = 1;
		foreach ($filas_a as $col) {
			if (!empty($col[0])) {
				$prod = Producto::where('origen', '=', $col[0])->first();
				$n_fila = $n_fila + 1;

				if (is_null($prod)) {
					array_push($no_existe, [
						'fila' => $n_fila,
						'sku' => $col[0],
					]);
				}
			} else {
				throw ValidationException::withMessages([
					'error_sku' => "SKU no debe estar vacio.",
				]);
			}
		}

		if (count($no_existe) > 0) {
			throw ValidationException::withMessages([
				'filas' => [$no_existe]
			]);
		} else {

			DB::beginTransaction();
			try {

				//importando excel
				Excel::import(new ProductoStockImport(), $file);
				DB::commit();
			} catch (Exception $e) {
				DB::rollBack();
				return [
					'success' => false,
					'message' => $e->getMessage(),
				];
			}
		}
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

	// actualiza yuanes en productos a valor 7
	public function actualizarYuanes()
	{

		$productos = Producto::all();

		foreach ($productos as $producto) {


			ProductoYuan::create([
				"producto_id" => $producto->id,
				"tipo_cambio_yuan_id" => 1,
			]);
		}
		return 'Yuanes Actualizado';
	}

	public function storeMasivo(ProductoMasivoStoreRequest $request)
	{

		$file = $request->file('archivo');
		$existe = [];
		$existe_codigo = [];
		$filas = Excel::toArray([], $file);
		$filas_a = array_slice($filas[0], 1);
		$n_fila = 1;
		foreach ($filas_a as $col) {
			if (!empty($col[0])) {
				$prod = Producto::where('origen', '=', strtoupper($col[0]))
					->where('codigo_barra', '=', $col[3])
					->first();
				$n_fila = $n_fila + 1;

				if (!is_null($prod)) {
					array_push($existe, [
						'fila' => $n_fila,
						'sku' => $col[0],
						'codigo_barra' => $col[3]
					]);
				}
			} else {
				throw ValidationException::withMessages([
					'error_sku' => "SKU no debe estar vacio.",
				]);
			}
		}
		if (count($existe) > 0) {
			throw ValidationException::withMessages([
				'filas' => [$existe]
			]);
		} else {

			DB::beginTransaction();
			try {

				//importando excel
				Excel::import(new ProductoMasivoImport(), $file);
				DB::commit();
			} catch (Exception $e) {
				DB::rollBack();
				return [
					'success' => false,
					'message' => $e->getMessage(),
				];
			}
		}
	}
}
