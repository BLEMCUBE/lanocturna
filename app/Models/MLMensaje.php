<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class MLMensaje extends Model
{
	protected $table = 'ml_mensajes';

	protected $fillable = [
		'client_id',
		'reclamo_id',
		'order_id',
		'pack_id',
		'message_id',
		'conversation_id',
		'from_user_id',
		'to_user_id',
		'text',
		'attachment_path',
		'date_created',
		'received_at',
		'is_read',
		'is_from_seller',
		'payload'
	];
	protected $casts = [
		'payload' => 'array',
		'date_created' => \App\Casts\UtcDatetime::class,
	];

	/**
	 * Relación por pack_id
	 */
	public function ventaPorPack()
	{
		return $this->belongsTo(MLOrden::class, 'pack_id', 'pack_id');
	}

	/**
	 * Relación alternativa por orden_id
	 */
	public function ventaPorId()
	{
		return $this->belongsTo(MLOrden::class, 'pack_id', 'orden_id');
	}

	/**
	 * Relación con el usuario (remitente)
	 */
	public function from_user()
	{
		return $this->belongsTo(MLListaUsuario::class, 'from_user_id', 'user_id');
	}

	/**
	 * Scope: cargar ventas (priorizando pack_id y fallback por venta_id)
	 */

	public function scopeWithVenta(Builder $query)
	{
		return $query
			->with(['ventaPorPack', 'ventaPorId'])
			->where(function ($q) {
				$q->whereHas('ventaPorPack')
					->orWhereHas('ventaPorId');
			});
	}

	 public function reclamo()
    {
        return $this->belongsTo(MLReclamo::class, 'reclamo_id');
    }
}
