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
		Schema::create('ml_ordenes', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('client_id')->nullable();
			$table->unsignedBigInteger('orden_id')->unique();
			$table->unsignedBigInteger('pack_id')->nullable(); // relación
			$table->string('buyer_id')->nullable();
			$table->string('seller_id')->nullable();
			$table->string('status')->default('pending');
			//$table->json('item_ids')->nullable();
			$table->timestamp('date_created')->nullable();
			$table->json('payload')->nullable(); // JSON completo
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('ml_ordenes');
	}
};
