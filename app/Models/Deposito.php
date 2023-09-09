<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deposito extends Model
{
    use  HasFactory;

    protected $table = 'depositos';
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

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }
}
