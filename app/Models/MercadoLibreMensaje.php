<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class MercadoLibreMensaje extends Model
{
	protected $table = 'mercadolibre_mensajes';

	protected $fillable = [
		'pack_id',
		'pack_status',
		'claim_id',
		'claim_status',
		'message_id',
		'from_user_id',
		'to_user_id',
		'body',
		'attachment_path',
		'date_created',
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
		return $this->belongsTo(MercadoLibreVenta::class, 'pack_id', 'pack_id');
	}

	/**
	 * Relación alternativa por mercadolibre_venta_id
	 */
	public function ventaPorId()
	{
		return $this->belongsTo(MercadoLibreVenta::class, 'pack_id', 'mercadolibre_venta_id');
	}

	/**
	 * Relación con el usuario (remitente)
	 */
	public function from_user()
	{
		return $this->belongsTo(MercadoLibreListaUsuario::class, 'from_user_id', 'user_id');
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
}
