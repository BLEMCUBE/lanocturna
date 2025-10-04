<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductoVariacion extends Model
{
    protected $fillable = ['producto_id', 'atributos', 'precio', 'stock'];

    protected $casts = [
        'atributos' => 'array',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
