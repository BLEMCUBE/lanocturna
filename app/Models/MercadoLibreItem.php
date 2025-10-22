<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MercadoLibreItem extends Model
{

	protected $table = 'mercadolibre_items';
	 protected $fillable = [
        'item_id',
        'title',
        'category_id',
        'seller_id',
        'status',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];


	public function preguntas()
	{
		return $this->hasMany(MercadoLibrePregunta::class, 'item_id', 'item_id');
	}
}
