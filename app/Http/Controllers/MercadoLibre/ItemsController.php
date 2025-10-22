<?php

namespace App\Http\Controllers\MercadoLibre;

use App\Http\Controllers\Controller;
use App\Services\MercadoLibreService;
use Illuminate\Support\Facades\Log;
use App\Models\MercadoLibreItem;

class ItemsController extends Controller
{
    protected $ml;

    public function __construct(MercadoLibreService $ml)
    {
        $this->ml = $ml;
    }

    public function handles(array $payload)
    {
        $resource = $payload['resource'] ?? null;
        $userId   = $payload['user_id'] ?? null;

        if (!$resource || !$userId) return;

        try {
            $item = $this->ml->apiGet($resource, $userId);


            MercadoLibreItem::updateOrCreate(
            ['item_id' => $item['id']],
            [
                'title' => $item['title'] ?? null,
                'category_id' => $item['category_id'] ?? null,
                'seller_id' => $item['seller_id'] ?? null,
                'status' => $item['status'] ?? null,
                'payload' => $item,
            ]

        );

            Log::info("Item actualizado [{$item['id']}]");

        } catch (\Exception $e) {
            Log::error("Error guardando item: " . $e->getMessage());
        }
    }
}
