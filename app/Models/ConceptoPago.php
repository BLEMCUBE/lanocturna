<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConceptoPago extends Model
{
    use HasFactory;

    protected $table = 'concepto_pago';
    protected $fillable = [
        'id',
        'nombre',
    ];


    public function pagos_servicios()
    {
		return $this->hasMany(PagoServicio::class,'concepto_pago_id','id');
    }

}
