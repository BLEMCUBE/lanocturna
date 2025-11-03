<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MercadoLibreUsuario extends Model
{
	protected $table = 'mercadolibre_usuarios';
	protected $fillable = [
		'meli_user_id',
		'nickname',
		'email',
		'cliente_id',
		'access_token',
		'refresh_token',
		'expires_at',
		'payload'
	];

	protected $casts = [
		'payload' => 'array'
	];
	public function cliente()
	{
		return $this->belongsTo(MercadoLibreCliente::class, 'cliente_id', 'id');
	}
}
