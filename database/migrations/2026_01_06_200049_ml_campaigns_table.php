<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::create('ml_campaigns', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('client_id')->nullable()->index();
			$table->unsignedBigInteger('campaign_id')->nullable()->index();
			$table->string('name')->nullable();
			$table->string('status')->nullable();
			$table->timestamp('date_created')->nullable();
			$table->timestamp('last_updated')->nullable();
			$table->string('strategy')->nullable();
			$table->string('channel')->nullable();
			$table->json('metrics')->nullable();
			$table->timestamps();
		});


		Schema::create('ml_campaign_items', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('client_id')->nullable()->index();
			$table->unsignedBigInteger('campaign_id')->nullable()->index();
			$table->unsignedBigInteger('ad_group_id')->nullable()->index();
			$table->unsignedBigInteger('advertiser_id')->nullable();
			$table->string('item_id')->nullable()->index();
			$table->string('sku')->nullable()->index();
			$table->string('status')->nullable();
			$table->date('fecha')->nullable()->index();
			$table->unsignedBigInteger('clicks')->default(0);
			$table->unsignedBigInteger('prints')->default(0);
			$table->decimal('direct_amount', 20, 2)->default(0);
			$table->decimal('indirect_amount', 20, 2)->default(0);
			$table->unsignedBigInteger('direct_units_quantity')->default(0);
			$table->unsignedBigInteger('indirect_units_quantity')->default(0);
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('ml_campaigns');
		Schema::dropIfExists('ml_campaign_items');
	}
};
