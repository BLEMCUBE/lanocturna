<?php

namespace App\Http\Controllers\MercadoLibre;

use App\Http\Controllers\Controller;
use App\Models\MLApp;
use App\Http\Requests\MLAppStoreRequest;
use App\Http\Requests\MLAppUpdateRequest;
use App\Http\Resources\MercadoLibreCollection;
use App\Models\MLItem;
use Illuminate\Support\Str;
use App\Models\MLOrden;
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
	public function publicidad($adId)
	{
		$cliente = MLApp::with('usuario')->whereHas('usuario', function ($query) use ($adId) {
			$query->where('ml_clients.meli_user_id', $adId);
		})->first();
		if (! $cliente->app_id) {
			return response()->json(['error' => 'Usuario ML no encontrado'], 404);
		}

		$client_id = $cliente->app_id;
		$ml = app(MercadoLibreService::class)->forClient($client_id);
		$response = $ml->apiGetDos(
			"/advertising/MLU/advertisers/" . $cliente->usuario->ad_id . "/product_ads/ads/search",
			$cliente->usuario->meli_user_id,
			[
				"date_from" => "2025-12-29",
				"date_to" => "2026-01-03",
				"filters[statuses]" => "active",
				"metrics" => "clicks,cpc,prints,cost,acos,direct_units_quantity,indirect_units_quantity,total_amount",
			]
		);

		if (! $response['success']) {
			return response()->json(['error' => 'Error al obtener datos de publicidad'], 500);
		}
		$datos = [];
		foreach ($response['body']['results'] as $ad) {
			$datos[] = [
				"item_id"=>$ad['item_id'],
				"sku" => $this->extractSkuFromMercadoLibrePayload($ad['item_id']),
				"sku2" => $this->getSku($ad['item_id']),
				//"direct_units_quantity"=>$ad['metrics']['direct_units_quantity'],
				"total_venta" => $ad['metrics']['direct_units_quantity'] + $ad['metrics']['indirect_units_quantity'],
				//"indirect_units_quantity"=>$ad['metrics']['indirect_units_quantity'],

			];
		}
		return response()->json([
			"tienda1" => $datos,
			//"tienda1" => $datos
		]);
	}

	public function getSku($itemId)
	{
		$item = MLOrden::where('item_id', $itemId)->first();
		return $item ? $item->item_sku : "";
	}

		public function getSkuItem($itemId)
	{

		$item = MLItem::where('item_id', $itemId)->first();
		//return $item ? $item->item_sku : "";
		return $item ? $item->payload : "";
	}
	function extractSkuFromMercadoLibrePayload($itemId) {
    // Buscar en la base de datos
    $item = DB::table('ml_items')
        ->whereRaw("payload->>'$.id' = ?", [$itemId])
        ->first();

    if (!$item) {
        $ord = MLOrden::where('item_id', $itemId)->first();
		return $ord ? $ord->item_sku : "no existe";
    }

    // Decodificar JSON
    $payload = json_decode($item->payload, true);

    // Buscar SKU en attributes (formato de tu ejemplo)
    if (isset($payload['attributes']) && is_array($payload['attributes'])) {
        foreach ($payload['attributes'] as $attribute) {
            // Verificar si es el atributo SKU
            if (isset($attribute['id']) && $attribute['id'] === 'SELLER_SKU') {
                // Tu ejemplo tiene el SKU en value_name
                if (isset($attribute['value_name']) && !empty($attribute['value_name'])) {
                    return $attribute['value_name']; // "MAR-3148"
                }

                // Algunos items podrían tenerlo en values[0].name
                if (isset($attribute['values'][0]['name']) && !empty($attribute['values'][0]['name'])) {
                    return $attribute['values'][0]['name'];
                }
            }
        }
    }

    return null;
}
}
