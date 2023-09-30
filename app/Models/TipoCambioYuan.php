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
}
