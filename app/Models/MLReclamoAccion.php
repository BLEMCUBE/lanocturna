<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MLReclamoAccion extends Model
{
	protected $table = 'ml_reclamo_acciones';

	protected $fillable = [
		'reclamo_id',
		'date',
		'payload',
	];

	protected $casts = [
		'payload' => 'array',
	];

	/* ================= RELACIONES ================= */




}
