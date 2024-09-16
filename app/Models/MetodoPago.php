<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetodoPago extends Model
{
    use HasFactory;

    protected $table = 'metodo_pago';
    protected $fillable = [
        'id',
        'nombre',
    ];


    public function pagos_servicios()
    {
		return $this->hasMany(PagoServicio::class,'metodo_pago_id','id');
    }

}
