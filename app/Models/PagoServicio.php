<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PagoServicio extends Model
{
    use  HasFactory;

    protected $table = 'pagos_servicios';
    protected $fillable = [
        'id',
        'fecha_pago',
        'moneda',
        'concepto_pago_id',
        'metodo_pago_id',
        'nro_factura',
        'monto',
        'observacion',
        'user_id',
        'created_at'

    ];

    public function concepto_pago()
    {
        return $this->belongsTo(ConceptoPago::class,'concepto_pago_id','id');
    }

	public function metodo_pago()
    {
        return $this->belongsTo(MetodoPago::class,'metodo_pago_id','id');
    }
    public function usuario()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }
}
