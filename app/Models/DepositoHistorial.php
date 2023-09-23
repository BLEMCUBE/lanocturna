<?php

namespace App\Models;

use App\Casts\Json;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepositoHistorial extends Model
{
    use  HasFactory;

    protected $table = 'depositos_historial';
    protected $fillable = [
        'id',
        'datos',
        'created_at'
    ];

    protected $casts = [
        'datos'=>Json::class
    ];


}
