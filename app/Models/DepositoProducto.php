<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepositoProducto extends Model
{
    use  HasFactory;

    protected $table = 'depositos_productos';
    protected $fillable = [
        'id',
        'sku',
        'pcs_bulto',
        'bultos',
        'cantidad_total',
        'deposito_lista_id'
    ];



    public function deposito_lista()
    {
        return $this->belongsTo(DepositoLista::class);
    }



    public function producto()
    {
        return $this->belongsTo(Producto::class,'sku','origen');

    }

	   public function costos_reales()
    {
        return $this->hasOne(CostoReal::class,'sku','sku');

    }
}
