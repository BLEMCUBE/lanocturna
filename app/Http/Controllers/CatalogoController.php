<?php

namespace App\Http\Controllers;

use App\Http\Requests\CatalogoUpdateRequest;
use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Support\Facades\Request;

class CatalogoController extends Controller
{
	public function __construct()
	{
		//protegiendo el controlador segun el rol
		$this->middleware(['auth', 'permission:menu-catalogo'])->only('index');
		$this->middleware(['auth', 'permission:imagen-catalogo'])->only(['update']);
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
		return Inertia::render('Catalogo/Index', [
			'lista_categorias' => $lista_categorias,
			'productos' => $productos_query,
			'filtro' => Request::only(['buscar', 'categoria'])
		]);
	}

	public function update(CatalogoUpdateRequest $request, $id)
	{
		$producto = Producto::find($id);
		$old_photo = $producto->imagen;
		$producto->save();

		//imagen
		if ($request->hasFile('imagen')) {
			sleep(1);
			$url_save = public_path() . $old_photo;
			$fileName = time() . '.' . $request->imagen->extension();
			//eliminar imagen
			if (file_exists($url_save) && $old_photo != "/images/productos/sin_foto.png") {
				unlink($url_save);
			}
			$producto->update([
				'imagen' => "/images/productos/" . $fileName
			]);
			$request->imagen->move(public_path('images/productos'), $fileName);
		}
	}
}
