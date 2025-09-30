<?php

namespace App\Http\Controllers;

use App\Http\Requests\AtributoStoreRequest;
use App\Http\Requests\AtributoUpdateRequest;
use App\Http\Resources\AtributoCollection;
use App\Models\Atributo;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AtributoController extends Controller
{
	public function index()
	{

		return Inertia::render('Atributos/Index', [
			'atributos' => new AtributoCollection(
				Atributo::orderBy('nombre', 'ASC')->with('valores')
					->get()
			)
		]);
	}


	public function store(AtributoStoreRequest $request)
	{
		Atributo::create($request->only('nombre'));
	}

	public function show($id)
	{
		$item = Atributo::with(['valores' => function ($query) {
			$query->select(DB::raw("id,valor,atributo_id"))
				->with(['productos' => function ($query) {
					$query->select(DB::raw("count(producto_id) as total"));
				}])->first();
		}])
			->findOrFail($id);
		return response()->json([
			"atributo" => $item
		]);
	}

	public function update(AtributoUpdateRequest $request, $id)
	{
		$item = Atributo::find($id);
		$item->nombre = $request->input('nombre');
		$item->save();
	}

	public function destroy($id)
	{
		$atributo = Atributo::find($id);
		$atributo->delete();
	}
}
