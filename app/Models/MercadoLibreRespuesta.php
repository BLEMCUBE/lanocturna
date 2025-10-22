<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MercadoLibreRespuesta extends Model
{

	protected $table = 'mercadolibre_respuestas';
	protected $fillable = [
		'pregunta_id',
		'from_user_id',
		'text',
		'date_created',
		'payload',
	];


	protected $casts = [
		'payload' => 'array',
	];

	/*public function cliente()
	{
		return $this->belongsTo(MercadoLibreCliente::class, 'cliente_id', 'id');
	}*/
}
