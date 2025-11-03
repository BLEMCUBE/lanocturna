<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MercadoLibreVenta extends Model
{

	protected $table = 'mercadolibre_ventas';
	 protected $fillable = [
        'mercadolibre_venta_id',
        'buyer_id',
        'seller_id',
        'status',
        'item_ids',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
		 'item_ids' => 'array',
    ];



	public function mensajes()
	{
		return $this->hasMany(MercadoLibreMensaje::class, 'mercadolibre_venta_id', 'mercadolibre_venta_id');
	}
}
