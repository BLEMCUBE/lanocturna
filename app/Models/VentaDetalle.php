<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VentaDetalle extends Model
{
    use  HasFactory;

    protected $table = 'venta_detalles';
    protected $fillable = [
        'id',
        'venta_id',
        'producto_id',
        'precio',
        'precio_iva',
        'cantidad',
        'total',
        'total_iva',
        'producto_validado',

    ];


    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);

    }

}
