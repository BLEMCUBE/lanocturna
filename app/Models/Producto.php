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
        'stock_minimo',
        'stock_futuro',
    ];

    /*public function detalles_ventas()
    {
        return $this->hasMany(VentaDetalle::class);
    }*/
}
