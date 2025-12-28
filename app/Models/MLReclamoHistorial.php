<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MLReclamoHistorial extends Model
{
	protected $table = 'ml_reclamo_historial';

	protected $fillable = [
		'reclamo_id',
		'description',
		'date',
		'payload',
	];

	protected $casts = [
		'payload' => 'array',
	];

	/* ================= RELACIONES ================= */




}
