<?php

namespace App\Http\Controllers;

use App\Http\Requests\AtributoValorStoreRequest;
use App\Http\Requests\AtributoValorUpdateRequest;
use App\Http\Resources\AtributoValorCollection;
use App\Models\Atributo;
use App\Models\AtributoValor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AtributoValorController extends Controller
{

	public function index($id)
	{
		return response()->json([
			"atributo" => new AtributoValorCollection(
				AtributoValor::orderBy('valor', 'ASC')
					->with('productos')
					->with('atributo')
					->where('atributo_id', '=', $id)
					->get()
			)
		]);
	}

	public function show($id)
	{
		$item = AtributoValor::findOrFail($id);
		return response()->json([
			"atributo" => $item
		]);
	}

	public function store(AtributoValorStoreRequest $request)
	{

		AtributoValor::create($request->only('valor','atributo_id'));

	}

	public function update(AtributoValorUpdateRequest $request, $id)
	{
		$item = AtributoValor::find($id);
		$item->valor = $request->input('valor');
		$item->atributo_id = $request->input('atributo_id');
		$item->save();
	}

	public function destroy($id)
	{
		$atributo = AtributoValor::find($id);
		$atributo->delete();
	}
}
