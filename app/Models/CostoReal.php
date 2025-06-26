<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CostoReal extends Model
{
	use  HasFactory;

	protected $table = 'costos_reales';
	protected $fillable = [
		'id',
		'fecha',
		'sku',
		'origen',
		'monto',
		'producto_id',
		'compra_detalle_id',
		'compra_id',
		'importaciones_detalle_id',
		'importacion_id',
		'creador_id'
	];


	public function producto()
	{
		return $this->belongsTo(Producto::class,'producto_id','id');
	}
	public function creador()
	{
		return $this->belongsTo(User::class, 'creador_id', 'id');
	}
		public function importacion_detalles()
	{
		return $this->belongsTo(ImportacionDetalle::class, 'importaciones_detalle_id', 'id');
	}

	public function latestReg($column = 'created_at')
	{
		return $this->orderBy($column, 'desc');
	}

}
