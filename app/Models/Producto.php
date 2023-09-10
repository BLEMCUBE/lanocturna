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

    public function detalles_ventas()
    {
        return $this->hasMany(VentaDetalle::class);
    }

    public function importacion_detalles()
    {
        return $this->hasMany(ImportacionDetalle::class,'codigo_barra','codigo_barra');
    }

    public function deposito_detalles()
    {
        return $this->hasMany(DepositoDetalle::class,'codigo_barra','codigo_barra');
    }
}
