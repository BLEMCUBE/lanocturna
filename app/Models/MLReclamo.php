<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MLReclamo extends Model
{

	protected $table = 'ml_reclamos';
	protected $fillable = [
		'meli_user_id',
		'reclamo_id',
		'order_id',
		'resource',
		'status',
		'reason',
		'substatus',
		'payload',
	];

	protected $casts = [
		'payload' => 'array',
	];


	public function client()
	{
		return $this->belongsTo(MlClient::class, 'meli_user_id', 'meli_user_id');
	}

	public function orden()
	{
		return $this->belongsTo(MLOrden::class, 'order_id', 'order_id');
	}

	public function mensajes()
{
    return $this->hasMany(MLMensaje::class, 'reclamo_id', 'reclamo_id');
}
}
