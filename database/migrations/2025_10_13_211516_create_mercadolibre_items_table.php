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
		Schema::create('mercadolibre_items', function (Blueprint $table) {
			$table->id();
			$table->string('item_id')->unique(); // ID del ítem en ML, ej: MLU123456
			$table->string('title')->nullable(); // Título del producto
			$table->string('category_id')->nullable();
			$table->string('seller_id')->nullable();
			$table->string('status')->nullable();
			$table->json('payload')->nullable(); // JSON completo del ítem
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('mercadolibre_items');
	}
};
