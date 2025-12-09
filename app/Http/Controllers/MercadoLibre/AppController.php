<?php

namespace App\Http\Controllers\MercadoLibre;

use App\Http\Controllers\Controller;
use App\Models\MLApp;
use App\Http\Requests\MLAppStoreRequest;
use App\Http\Requests\MLAppUpdateRequest;
use App\Http\Resources\MercadoLibreCollection;
use Inertia\Inertia;

class AppController extends Controller
{

	public function index()
	{
		$items = new MercadoLibreCollection(
			MLApp::with('usuario')->orderBy('nombre', 'ASC')
				->get()
		);
		return Inertia::render('MercadoLibre/Apps', [
			'items' => $items
		]);
	}

	public function store(MLAppStoreRequest $request)
	{
		$request->merge(['redirect_uri' => route('mercadolibre.callback')]);
		MLApp::create($request->all());
	}

	public function show($id)
	{
		$item = MLApp::findOrFail($id);
		return response()->json([
			"item" => $item
		]);
	}

	public function update(MLAppUpdateRequest $request, $id)
	{
		$item = MLApp::findOrFail($id);
		$request->merge(['redirect_uri' => route('mercadolibre.callback')]);
		$item->update($request->all());
	}

	public function destroy($id)
	{
		$item = MLApp::find($id);
		$item->delete();
	}
}
