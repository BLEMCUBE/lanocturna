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
		'buyer_id',
		'seller_id',
		'status',
		'item_ids',
		'date_created',
		'payload',
	];

	protected $casts = [
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
}
