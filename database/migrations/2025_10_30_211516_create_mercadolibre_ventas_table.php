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
		Schema::create('mercadolibre_ventas', function (Blueprint $table) {
			$table->id();
			$table->string('mercadolibre_venta_id')->unique();
			$table->string('buyer_id')->nullable();
			$table->string('seller_id')->nullable();
			$table->string('status')->default('pending');
			$table->json('item_ids')->nullable();
			$table->json('payload')->nullable(); // JSON completo de la venta
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('mercadolibre_ventas');
	}
};
