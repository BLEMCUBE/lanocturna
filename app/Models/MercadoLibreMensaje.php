<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MercadoLibreMensaje extends Model
{

	protected $table = 'mercadolibre_mensajes';
	 protected $fillable = [
        'mercadolibre_venta_id',
        'message_id',
        'sender_id',
        'receiver_id',
        'body',
        'status',
        'read',
        'payload',
    ];


    protected $casts = [
        'payload' => 'array',
    ];


	public function venta()
	{
		return $this->belongsTo(MercadoLibreVenta::class, 'mercadolibre_venta_id', 'mercadolibre_venta_id');
	}
}
