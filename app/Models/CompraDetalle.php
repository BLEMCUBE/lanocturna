<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompraDetalle extends Model
{
    use  HasFactory;

    protected $table = 'compra_detalles';
    protected $fillable = [
        'id',
        'compra_id',
        'producto_id',
        'precio',
        'precio_sin_iva',
        'cantidad',
        'total',
        'total_sin_iva',
        'producto_validado',

    ];


    public function compra()
    {
        return $this->belongsTo(Compra::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);

    }
	 public function costo_reales()
    {
        return $this->belongsTo(CostoReal::class,'producto_id','producto_id');

    }

}
