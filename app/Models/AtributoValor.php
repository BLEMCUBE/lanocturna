<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AtributoValor extends Model
{
	protected $table = 'atributo_valores';
	protected $fillable = ['id','atributo_id', 'valor'];

	public function atributo()
	{
		return $this->belongsTo(Atributo::class);
	}

	public function productos()
	{
		return $this->belongsToMany(Producto::class, 'atributo_valor_producto');
	}
}
