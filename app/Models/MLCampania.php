<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MLCampania extends Model
{
	protected $table = 'ml_campaigns';

	protected $fillable = [
		'client_id',
		'campaign_id',
		'name',
		'status',
		'date_created',
		'last_updated',
		'strategy',
		'channel',
		'metrics'
	];

	protected $casts = [
		'metrics' => 'array',
	];

	public function detalles()
	{
		return $this->hasMany(MLCampaniaItem::class, 'campaign_id', 'campaign_id');
	}
}
