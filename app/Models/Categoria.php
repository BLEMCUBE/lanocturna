<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    use HasFactory;

    protected $table = 'categorias';
    protected $fillable = [
        'id',
        'name',
        'code',
    ];

        
    public function productos()
    {
        //return $this->belongsToMany(Producto::class, 'categoria_producto', 'categoria_id', 'producto_id');
        return $this->belongsToMany(Producto::class)
        ->withTimestamps();
    }

}
