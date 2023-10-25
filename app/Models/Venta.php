<?php

namespace App\Models;

use App\Casts\Json;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Venta extends Model
{
    use  HasFactory;

    protected $table = 'ventas';
    protected $fillable = [
        'id',
        'codigo',
        'total',
        'total_sin_iva',
        'moneda',
        'tipo_cambio',
        'observaciones',
        'facturado',
        'validado',
        'estado',
        'motivo_anulacion',
        'fecha_anulacion',
        'fecha_facturacion',
        'fecha_anulacion',
        'destino',
        'cliente',
        'tipo',
        'nro_compra',
        'parametro',
        'vendedor_id',
        'facturador_id',
        'validador_id',
        'created_at'
    ];

    protected $casts = [
        'cliente'=>Json::class,
        'parametro'=>Json::class,
    ];

    public function vendedor()
    {
        return $this->belongsTo(User::class);
    }

    public function facturador()
    {
        return $this->belongsTo(User::class);
    }

    public function validador()
    {
        return $this->belongsTo(User::class);
    }

    public function detalles_ventas()
    {
        return $this->hasMany(VentaDetalle::class);
    }

    /*1er argumento -- clase del modelo relacionado
    2do argumento -- nombre de la tabla pivote-intermedio
    3er argumento -- FK de esta modelo "mapeado"
    3to argumento -- FK del otro modelo relacionado*/
}
