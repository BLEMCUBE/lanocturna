<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MercadoLibreListaUsuario extends Model
{

	protected $table = 'mercadolibre_lista_usuarios';
	 protected $fillable = [
        'user_id',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

	public function preguntas()
	{
		return $this->hasMany(MercadoLibrePregunta::class, 'from_user_id', 'user_id');
	}

	public function mensajes()
	{
		return $this->hasMany(MercadoLibreMensaje::class, 'from_user_id', 'user_id');
	}

	public function venta()
	{
		return $this->belongsTo(MercadoLibreVenta::class, 'user_id', 'buyer_id');
	}
}
