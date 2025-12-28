<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MLOrden extends Model
{

	protected $table = 'ml_ordenes';
	protected $fillable = [
		'id',
		'client_id',
		'pack_id',
		'orden_id',
		'envio_id',
		'buyer_id',
		'seller_id',
		'status',
		'item_ids',
		'date_created',
		'envio',
		'facturacion',
		'payload',
		'shipping_paid_by',
		'shipping_buyer_cost',
		'shipping_seller_cost',
		'shipping_detected_by'
	];

	protected $casts = [
		'envio' => 'array',
		'facturacion' => 'array',
		'payload' => 'array',
		'item_ids' => 'array',
	];


	// 🔹 Mensajes vinculados por pack_id
	public function mensajesPorPack()
	{
		return $this->hasMany(MLMensaje::class, 'pack_id', 'pack_id');
	}

	// 🔹 Mensajes vinculados por orden_id
	public function mensajesPorId()
	{
		return $this->hasMany(MLMensaje::class, 'pack_id', 'orden_id');
	}

	public function comprador()
	{
		return $this->hasOne(MLListaUsuario::class, 'user_id', 'buyer_id');
	}

	public function getItemsAttribute()
	{
		return MLItem::whereIn('item_id', $this->item_ids ?? [])->get();
	}

	public function reclamos()
	{
		return $this->hasMany(MLReclamo::class, 'resource_id', 'order_id');
	}

	public function items()
	{
		return MLItem::whereIn('item_id', $this->item_ids)->get();
	}
}
