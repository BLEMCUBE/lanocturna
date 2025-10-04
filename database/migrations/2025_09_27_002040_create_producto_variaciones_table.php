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
		Schema::create('producto_variaciones', function (Blueprint $table) {
			$table->id();
			$table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
			$table->json('atributos'); // { "color": "Rojo", "talla": "M" }
			$table->decimal('precio', 10, 2)->nullable();
			$table->integer('stock')->default(0);
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('producto_variaciones');
	}
};
