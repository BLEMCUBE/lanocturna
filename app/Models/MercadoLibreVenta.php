<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MercadoLibreVenta extends Model
{

	protected $table = 'mercadolibre_ventas';
	protected $fillable = [
		'id',
		'mercadolibre_venta_id',
		'buyer_id',
		'pack_id',
		'pack_status',
		'claim_id',
		'claim_status',
		'seller_id',
		'status',
		'item_ids',
		'payload',
	];

	protected $casts = [
		'payload' => 'array',
		'item_ids' => 'array',
	];


	// 🔹 Mensajes vinculados por pack_id
	public function mensajesPorPack()
	{
		return $this->hasMany(MercadoLibreMensaje::class, 'pack_id', 'pack_id');
	}

	// 🔹 Mensajes vinculados por mercadolibre_venta_id
	public function mensajesPorId()
	{
		return $this->hasMany(MercadoLibreMensaje::class, 'pack_id', 'mercadolibre_venta_id');
	}

	public function comprador()
	{
		return $this->hasOne(MercadoLibreListaUsuario::class, 'user_id', 'buyer_id');
	}


	public function getItemsAttribute()
	{
		return MercadoLibreItem::whereIn('item_id', $this->item_ids ?? [])->get();
	}
}
