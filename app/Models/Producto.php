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
        'en_camino',
        'arribado',
    ];

    public function detalles_ventas()
    {
        return $this->hasMany(VentaDetalle::class);
    }
    public function detalles_compras()
    {
        return $this->hasMany(CompraDetalle::class);
    }

    public function importacion_detalles()
    {
        return $this->hasMany(ImportacionDetalle::class,'sku','origen');
    }

    public function deposito_detalles()
    {
        return $this->hasMany(DepositoDetalle::class,'sku','origen');
    }
}
