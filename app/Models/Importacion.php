<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Importacion extends Model
{
    use  HasFactory;

    protected $table = 'importaciones';
    protected $fillable = [
        'id',
        'nro_carpeta',
        'nro_contenedor',
        'estado',
        'total',
        'fecha_arribado',
        'fecha_camino',
        'mueve_stock',
        'user_id',

    ];

    public function importaciones_detalles()
    {
        return $this->hasMany(ImportacionDetalle::class);
    }
}
