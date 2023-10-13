<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RmaDetalle extends Model
{
    use  HasFactory;

    protected $table = 'rma_detalles';
    protected $fillable = [
        'id',
        'rma_id',
        'producto_id',
        'cantidad',
        'precio',
        'total',
        'nro_serie',
        'defecto',

    ];

    public function venta()
    {
        return $this->belongsTo(Rma::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);

    }

}
