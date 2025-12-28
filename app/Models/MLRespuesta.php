<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MLRespuesta extends Model
{

	protected $table = 'ml_respuestas';
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

	  public function pregunta()
    {
        //return $this->belongsTo(MLPregunta::class, 'pregunta_id', 'pregunta_id');
        return $this->belongsTo(MLPregunta::class);
    }

}
