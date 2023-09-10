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
        'precio',
        'unidad',
        'pcs_bulto',
        'cantidad_total',
        'valor_total',
        'cbm_bulto',
        'cbm_total',
        'bultos',
        'codigo_barra',
        'importacion_id',
        'deposito_id',
        'user_id'

    ];



    public function deposito()
    {
        return $this->belongsTo(Deposito::class);
    }


    public function importacion()
    {
        return $this->belongsTo(Importacion::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class,'codigo_barra','codigo_barra');

    }
}
