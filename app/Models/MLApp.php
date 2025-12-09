<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MLApp extends Model
{
	use HasFactory;
	protected $table = 'ml_apps';
	protected $fillable = [
		'id',
		'nombre',
		'app_id',
		'client_secret',
		'is_default',
		'redirect_uri',
	];

	public function usuario()
	{
		return $this->hasOne(MLCLient::class, 'app_id', 'id');
	}
}
