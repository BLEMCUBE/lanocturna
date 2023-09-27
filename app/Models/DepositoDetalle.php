<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepositoDetalle extends Model
{
    use  HasFactory;

    protected $table = 'depositos_detalles';
    protected $fillable = [
        'id',
        'sku',
        'pcs_bulto',
        'bultos',
        'cantidad_total',
        'deposito_id'
    ];



    public function deposito()
    {
        return $this->belongsTo(Deposito::class);
    }



    public function producto()
    {
        return $this->belongsTo(Producto::class,'sku','origen');

    }
}
