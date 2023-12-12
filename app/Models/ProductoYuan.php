<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductoYuan extends Model
{
    use  HasFactory;

    protected $table = 'productos_yuanes';
    protected $fillable = [
        'id',
        'producto_id',
        'tipo_cambio_yuan_id',
        'importacion_id',
        'created_at',
        'updated_at',

    ];


      public function producto()
    {
        return $this->belongsTo(Producto::class);

    }

}
