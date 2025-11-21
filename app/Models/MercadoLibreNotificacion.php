<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MercadoLibreNotificacion extends Model
{
	protected $table = 'mercadolibre_notificaciones';
	protected $fillable = [
		'topic',
		'resource',
		'user_id',
		'actions',
		'application_id',
		'sent_at',
		'attempts',
		'status',
		'payload',
	];

	protected $casts = [
		'payload' => 'array'
	];
}
