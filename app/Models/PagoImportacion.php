<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PagoImportacion extends Model
{
    use  HasFactory;

    protected $table = 'pagos_importaciones';
    protected $fillable = [
        'id',
        'fecha_pago',
        'banco',
        'nro_transaccion',
        'monto',
        'importacion_id',
        'created_at'

    ];

    public function importacion()
    {
        return $this->belongsTo(Importacion::class);
    }
}
