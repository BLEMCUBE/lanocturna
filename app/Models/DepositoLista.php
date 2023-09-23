<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepositoLista extends Model
{
    use  HasFactory;

    protected $table = 'depositos_listas';
    protected $fillable = [
        'id',
        'nombre',
        'descripcion',
        'user_id',

    ];

    public function depositos_detalles()
    {
        return $this->hasMany(DepositoDetalle::class);
    }
    public function depositos_productos()
    {
        return $this->hasMany(DepositoProducto::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }
}
