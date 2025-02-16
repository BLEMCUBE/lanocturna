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
		'costo_real',
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
        'importacion_id',
        'created_at'

    ];



    public function importacion()
    {
        return $this->belongsTo(Importacion::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class,'sku','origen');

    }
}
