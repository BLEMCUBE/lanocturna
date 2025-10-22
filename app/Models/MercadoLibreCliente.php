<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MercadoLibreCliente extends Model
{
    use HasFactory;
	   protected $table = 'mercadolibre_clientes';
	     protected $fillable = [
        'nombre',
        'client_id',
        'client_secret',
        'is_default',
        'redirect_uri',
    ];

	 public function usuario()
    {
		return $this->hasOne(MercadoLibreUsuario::class,'cliente_id','id');
    }
}
