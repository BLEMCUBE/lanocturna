<?php

namespace App\Http\Controllers\MercadoLibre;

use App\Http\Controllers\Controller;
use App\Models\MLApp;
use App\Http\Requests\MLAppStoreRequest;
use App\Http\Requests\MLAppUpdateRequest;
use App\Http\Resources\MercadoLibreCollection;
use App\Jobs\PublicidadJob;
use App\Models\MLCampania;
use App\Models\MLItem;
use Illuminate\Support\Str;
use App\Models\MLOrden;
use App\Services\MercadoLibre\CampaniaService;
use Illuminate\Support\Facades\Log;
use App\Services\MercadoLibre\MercadoLibreService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
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

	//actualizar campos
	public function actualizar($tipo)
	{
		switch ($tipo) {

			case 'items':
				$items = MLItem::whereNull('last_updated')->get();
				foreach ($items as $key => $value) {
					$fActu = $value->payload;
					MLItem::where('item_id', $value['item_id'])
						->update([
							'last_updated' => $fActu['last_updated']
						]);
				}
				return 'OK';
				break;
			case 'orden':
				$items = MLOrden::whereNull('last_updated')
					->orwhereNull('date_created')->get();
				foreach ($items as $key => $value) {
					$fActu = $value->payload;
					$item = collect($fActu['order_items'])
						->pluck('item.id')
						->filter()
						->values()
						->toArray();
					$item_sku = collect($fActu['order_items'])
						->pluck('item.seller_sku')
						->filter()
						->values()
						->toArray();

					MLOrden::where('orden_id', $value['orden_id'])
						->update([
							'date_created' => $fActu['date_created'],
							'item_id' => $item[0],
							'item_sku' => Str::upper($item_sku[0] ?? ''),
							'last_updated' => $fActu['last_updated']
						]);
				}
				return 'OK';
				break;
			case 'test':
				$items = MLItem::get();
				$la = $items[100]['last_updated'];
				$la2 = $items[100]['payload']['last_updated'];
				$mysqlFormat1 = Carbon::parse($la)->format('Y-m-d H:i');
				$mysqlFormat2 = Carbon::parse($la2)->format('Y-m-d H:i');

				$igual = $mysqlFormat1 !== $mysqlFormat2;
				dd($igual);


				break;

			default:
				# code...
				break;
		}
	}
}
