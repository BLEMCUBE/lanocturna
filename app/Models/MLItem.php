<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MLItem extends Model
{

	protected $table = 'ml_items';
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
		return $this->hasMany(MLPregunta::class, 'item_id', 'item_id');
	}
}
