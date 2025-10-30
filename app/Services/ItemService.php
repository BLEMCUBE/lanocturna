<?php

namespace App\Services;

use App\Models\MercadoLibreItem;

class ItemService
{
	public function updateOrCreate($item)
	{
		$row = MercadoLibreItem::where('item_id', '=', $item['id'])->first();
		if ($row === null) {
			$data =	MercadoLibreItem::updateOrCreate(
				['item_id' => $item['id']],
				[
					'title' => $item['title'] ?? null,
					'category_id' => $item['category_id'] ?? null,
					'seller_id' => $item['seller_id'] ?? null,
					'status' => $item['status'] ?? null,
					'payload' => $item,
				]
			);
			return $data;
		}
		return null;
	}
}
