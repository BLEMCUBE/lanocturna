<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MercadoLibrePregunta extends Model
{

	protected $table = 'mercadolibre_preguntas';
	protected $fillable = [
		'mercadolibre_pregunta_id',
		'item_id',
		'seller_id',
		'from_user_id',
		'date_created',
		'text',
		'is_read',
		'status',
		'payload',
	];

	protected $casts = [
		'payload' => 'array',
	];

	public function item()
	{
		return $this->belongsTo(MercadoLibreItem::class, 'item_id', 'item_id');
	}

	public function from_user()
	{
		return $this->belongsTo(MercadoLibreListaUsuario::class, 'from_user_id', 'user_id');
	}
}
