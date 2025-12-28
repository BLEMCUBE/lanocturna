<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MLEnvio extends Model
{
	protected $table = 'ml_envios';

	protected $fillable = [
		'envio_id',
		'orden_id',
		'estado',
		'modo_envio',
		'nro_rastreo',
		'fecha_envio',
		'fecha_entrega',
		'costo',
		'payload',
	];

	protected $casts = [
		'costo' => 'array',
		'payload' => 'array',
	];

	/* ================= RELACIONES ================= */

	public function orden()
	{
		return $this->belongsTo(MLOrden::class, 'order_id', 'order_id');
	}

	public function reclamos()
	{
		return $this->hasMany(MLReclamo::class, 'resource_id', 'envio_id')
			->where('resource', 'shipment');
	}

	/* ================= HELPERS ================= */

	public function isDelivered(): bool
	{
		return $this->status === 'delivered';
	}

	public function isShipped(): bool
	{
		return $this->status === 'shipped';
	}
}
