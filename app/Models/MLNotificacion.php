<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MLNotificacion extends Model
{
	protected $table = 'ml_notificaciones';
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
