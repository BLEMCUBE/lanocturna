<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoCambioYuan extends Model
{
    use HasFactory;
    protected $table = 'tipo_cambio_yuanes';
    protected $fillable = [
        'id',
        'valor',
        'created_at'
    ];

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'productos_yuanes', 'tipo_cambio_yuan_id', 'producto_id');
    }

}
