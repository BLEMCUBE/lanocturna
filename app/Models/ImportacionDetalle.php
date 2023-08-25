<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportacionDetalle extends Model
{
    use  HasFactory;

    protected $table = 'importaciones_detalles';
    protected $fillable = [
        'id',
        'sku',
        'precio',
        'unidad',
        'pcs_bulto',
        'cantidad_total',
        'valor_total',
        'cbm_bulto',
        'cbm_total',
        'bultos',
        'codigo_barra',
        'estado',
        'mueve_stock',
        'importacion_id'

    ];



    public function importacion()
    {
        return $this->hasMany(Importacion::class);
    }
}
