<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MLPregunta extends Model
{

	protected $table = 'ml_preguntas';
	protected $fillable = [
		'pregunta_id',
		'item_id',
		'seller_id',
		'from_user_id',
		'date_created',
		'text',
		'is_read',
		'status',
		'payload'
	];

	protected $casts = [
		'payload' => 'array',
	];

	public function item()
	{
		return $this->belongsTo(MLItem::class, 'item_id', 'item_id');
	}

	public function from_user()
	{
		return $this->belongsTo(MLListaUsuario::class, 'from_user_id', 'user_id');
	}
	  public function respuesta()
    {
        return $this->hasOne(MLRespuesta::class, 'pregunta_id', 'pregunta_id');
    }
}
