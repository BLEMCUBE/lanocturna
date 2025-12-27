<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MLReclamoMensaje extends Model
{
	protected $table = 'ml_reclamo_mensajes';

	protected $fillable = [
		'reclamo_id',
		'hash',
		'sender_role',
		'receiver_role',
		'text',
		'attachment_path',
		'date_created',
		'date_read',
		'translated',
		'payload',
	];

	protected $casts = [
		'payload'    => 'array',
		//'date_created' => \App\Casts\UtcDatetime::class,
		//'date_read' => \App\Casts\UtcDatetime::class,
	];

	 /* Helpers */
    public function isFromBuyer(): bool
    {
        return $this->sender_role === 'complainant';
    }

    public function isFromSeller(): bool
    {
        return $this->sender_role === 'respondent';
    }

    public function isFromML(): bool
    {
        return $this->sender_role === 'mediator';
    }

		public function reclamo()
	{
		return $this->belongsTo(MLReclamo::class, 'reclamo_id', 'reclamo_id');
	}
}
