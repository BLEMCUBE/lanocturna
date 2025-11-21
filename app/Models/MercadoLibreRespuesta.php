<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MercadoLibreRespuesta extends Model
{

	protected $table = 'mercadolibre_respuestas';
	protected $fillable = [
		'mercadolibre_pregunta_id',
		'from_user_id',
		'text',
		'date_created',
		'payload',
	];


	protected $casts = [
		'payload' => 'array',
	];

	  public function pregunta()
    {
        return $this->belongsTo(MercadoLibrePregunta::class, 'mercadolibre_pregunta_id', 'mercadolibre_pregunta_id');
    }

}
