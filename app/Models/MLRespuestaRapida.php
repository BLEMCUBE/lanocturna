<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MLRespuestaRapida extends Model
{
	protected $table = 'ml_respuestas_rapidas';
	protected $fillable = [
		'titulo',
		'descripcion',
		'color',
		'tipo'
	];
}
