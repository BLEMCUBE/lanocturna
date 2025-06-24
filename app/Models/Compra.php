<?php

namespace App\Models;

use App\Casts\Json;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Compra extends Model
{
	use  HasFactory;

	protected $table = 'compras';
	protected $fillable = [
		'id',
		'nro_factura',
		'proveedor',
		'facturador_id',
		'estado',
		'fecha_anulacion',
		'observaciones',
		'total',
		'total_sin_iva',
		'moneda',
		'pagado',
		'tipo_cambio',
		'comprador_id',
		'created_at'
	];


	public function facturador()
	{
		return $this->belongsTo(User::class);
	}

	public function detalles_compras()
	{
		return $this->hasMany(CompraDetalle::class);
	}
	public function compra_pagos()
    {
        return $this->hasMany(PagoCompra::class);
    }
}
