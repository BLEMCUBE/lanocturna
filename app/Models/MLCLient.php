<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MLCLient extends Model
{
	protected $table = 'ml_clients';
	protected $fillable = [
		'meli_user_id',
		'nickname',
		'email',
		'app_id',
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
		//return $this->belongsTo(MLApp::class, 'app_id', 'id');
		return $this->belongsTo(MLApp::class);
	}
}
