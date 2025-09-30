<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
	use  HasFactory;

	protected $table = 'productos';
	protected $fillable = [
		'id',
		'origen',
		'nombre',
		'aduana',
		'codigo_barra',
		'imagen',
		'stock',
		'precio',
		'stock_minimo',
		'stock_futuro',
		'en_camino',
		'arribado',
		'created_at'
	];

	public function detalles_ventas()
	{
		return $this->hasMany(VentaDetalle::class);
	}
	public function detalles_rmas()
	{
		return $this->hasMany(RmaDetalle::class);
	}
	public function rmas()
	{
		return $this->hasMany(Rma::class);
	}
	public function detalles_compras()
	{
		return $this->hasMany(CompraDetalle::class);
	}

	public function importacion_detalles()
	{
		return $this->hasMany(ImportacionDetalle::class, 'sku', 'origen');
	}

	public function deposito_detalles()
	{
		return $this->hasMany(DepositoDetalle::class, 'sku', 'origen');
	}
	public function stock_rma()
	{
		return $this->hasMany(RmaStock::class, 'sku', 'origen');
	}

	public function yuanes()
	{
		return $this->belongsToMany(TipoCambioYuan::class, 'productos_yuanes', 'producto_id', 'tipo_cambio_yuan_id');
	}

	public function categorias()
	{
		//return $this->belongsToMany(Categoria::class, 'categoria_producto', 'producto_id', 'categoria_id');
		return $this->belongsToMany(Categoria::class)
			->withTimestamps();
	}

	public function costos_reales()
	{
		return $this->hasMany(CostoReal::class, 'producto_id', 'id');
	}

	  public function atributoValores()
    {
        return $this->belongsToMany(AtributoValor::class, 'atributo_valor_producto');
    }

    public function variaciones()
    {
        return $this->hasMany(ProductoVariacion::class);
    }


}
