<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MLClient extends Model
{
	protected $table = 'ml_clients';
	protected $fillable = [
		'meli_user_id',
		'nickname',
		'email',
		'app_id',
		'access_token',
		'refresh_token',
		'ad_id',
		'expires_at',
		'payload'
	];

	protected $casts = [
		'payload' => 'array'
	];
	public function cliente()
	{
		return $this->belongsTo(MLApp::class, 'app_id', 'id');
	}

	/** Relación con reclamos  */
	public function reclamos()
	{
		return $this->hasMany(MLReclamo::class, 'meli_user_id', 'meli_user_id');
	}
}
