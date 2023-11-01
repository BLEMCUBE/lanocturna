<?php

namespace App\Models;

use App\Casts\Json;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Rma extends Model
{
    use  HasFactory;

    protected $table = 'rmas';
    protected $fillable = [
        'id',
        'nro_servicio',
        'fecha_ingreso',
        'fecha_limite',
        'fecha_entrega',
        'fecha_compra',
        'nro_factura',
        'cliente',
        'estado',
        'modo',
        'tipo',
        'observaciones',
        'defecto',
        'producto_id',
        'prod_cantidad',
        'prod_origen',
        'prod_nombre',
        'prod_serie',
        'costo_presupuestado',
        'vendedor_id',
        'created_at'
    ];




    protected $casts = [
        'cliente'=>Json::class
    ];

    public function vendedor()
    {
        return $this->belongsTo(User::class);
    }


    public function detalles_rmas()
    {
        return $this->hasMany(VentaDetalle::class);
    }
    public function rma_stock()
    {
        return $this->hasOne(RmaStock::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);

    }


}
