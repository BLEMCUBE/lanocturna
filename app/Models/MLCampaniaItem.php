<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MLCampaniaItem extends Model
{
	protected $table = 'ml_campaign_items';

	protected $fillable = [
		'client_id',
		'campaign_id',
		'ad_group_id',
		'sku',
		'advertiser_id',
		'item_id',
		'status',
		'fecha',
		'clicks',
		'prints',
		'direct_amount',
		'indirect_amount',
		'direct_units_quantity',
		'indirect_units_quantity',
	];

	protected $casts = [];

	/* ================= RELACIONES ================= */

	public function campania()
	{
		return $this->belongsTo(MLCampania::class, 'campaign_id', 'campaign_id');
	}
}
