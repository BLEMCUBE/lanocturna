<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MLReclamo extends Model
{
    protected $table = 'ml_reclamos';

    protected $fillable = [
        'meli_user_id',
        'reclamo_id',
        'resource',
        'resource_id',
        'status',
        'reason',
        'reason_id',
        'stage',
        'date_created',
		'last_updated',
        'type',
        'reputacion',
        'detalle',
        'motivos',
        'payload',
    ];

    protected $casts = [
        'payload'    => 'array',
        'detalle'    => 'array',
        'motivos'    => 'array',
        'reputacion' => 'array',
    ];

    /* ================= RELACIONES ================= */

    public function orden()
    {
        return $this->belongsTo(MLOrden::class, 'resource_id', 'orden_id');
    }

    public function envio()
    {
        return $this->belongsTo(MLEnvio::class, 'resource_id', 'envio_id');
    }

    public function mensajes()
    {
        return $this->hasMany(MLReclamoMensaje::class, 'reclamo_id', 'reclamo_id');
    }

    /* ================= HELPERS ================= */

    public function isOrder(): bool
    {
        return $this->resource === 'order';
    }

    public function isShipment(): bool
    {
        return $this->resource === 'shipment';
    }


}
