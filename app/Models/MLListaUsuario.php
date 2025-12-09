<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MLListaUsuario extends Model
{

	protected $table = 'ml_lista_usuarios';
	 protected $fillable = [
        'user_id',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

	public function preguntas()
	{
		return $this->hasMany(MLPregunta::class, 'from_user_id', 'user_id');
	}

	public function mensajes()
	{
		return $this->hasMany(MLMensaje::class, 'from_user_id', 'user_id');
	}

	public function venta()
	{
		return $this->belongsTo(MLOrden::class, 'user_id', 'buyer_id');
	}
}
