<?php

namespace App\Services\MercadoLibre;

use App\Models\MLOrden;
use App\Traits\BaseMLService;
use Illuminate\Support\Facades\Log;

class OrdenService
{
	use BaseMLService;

    public function __construct(
        private MercadoLibreService $ml,
        private ItemService $itemService,
        private UsuarioService $mLUsuarioService
    ) {

    }

    /**
     * Crear o actualizar venta
     */
    public function updateOrCreate($item,$clientId)
    {
		$this->forClient($clientId);
        $meli_user_id = $this->usuarioMeliId();
		if (!$meli_user_id) return;
        // Registrar usuario comprador y vendedor si no existen
        $this->mLUsuarioService->buscarUsuario($item['buyer']['id'], $this->clienteId());
        $this->mLUsuarioService->buscarUsuario($item['seller']['id'], $this->clienteId());

        // Obtener lista de item_ids asociados a esta venta
        $items = collect($item['order_items'])
            ->pluck('item.id')
            ->filter()
            ->values()
            ->toArray();

        // Guardar venta
		$venta=[];
		$exite=MLOrden::where('orden_id',$item['id'])->whereNot('status','paid')
		->first();
		if(!$exite){

			$venta = MLOrden::updateOrCreate(
				['orden_id' => $item['id']],
				[
					'client_id'   => $this->clienteId(),
					'orden_id' => $item['id'],
					'pack_id'     => $item['pack_id'] ?? null,
					'buyer_id'    => $item['buyer']['id'] ?? null,
					'seller_id'   => $item['seller']['id'] ?? null,
					'status'      => $item['status'] ?? 'pending',
					'payload'     => $item,
					'item_ids'    => $items,
					]
				);

				// Registrar detalles de los items
				foreach ($items as $itemId) {
					$itemData = $this->mlForClient()->apiGet('/items/' . $itemId, $meli_user_id);
					$this->itemService->updateOrCreate($itemData);
				}
			}

        return $venta;
    }


    /**
     * Notificación desde Mercado Libre
     */
    public function storeNotificacion($payload)
    {
		$appId = $payload['application_id'] ?? null;

		if (! $appId) {
			Log::warning('MensajeService sin application_id', $payload);
			return;
		}
		$this->forClient($appId);
        $resource = $payload['resource'] ?? null;
        $userId   = $payload['user_id'] ?? null;

        if (!$resource || !$userId) return;

        $response = $this->mlForClient()->apiGetDos($resource, $userId);

        if ($response['success']) {

            $order = $this->updateOrCreate($response['body'],$this->clienteId());
            Log::info("Venta registrada para CLIENTE {$this->clienteId()}", [
                'venta_id' => $response['body']['id']
            ]);
        }

        $this->ml->actualizar($resource);
    }


    /**
     * Obtener comprador por ID de venta
     */
    public function comprador($orderId)
    {
        $venta = MLOrden::where('id', $orderId)->first();
        if (!$venta || !$venta->payload) return [];

        $item = $venta->payload;

        return [
            "id"         => $item['buyer']['id'],
            "nickname"   => $item['buyer']['nickname'],
            "last_name"  => $item['buyer']['last_name'],
            "first_name" => $item['buyer']['first_name'],
        ];
    }
}
