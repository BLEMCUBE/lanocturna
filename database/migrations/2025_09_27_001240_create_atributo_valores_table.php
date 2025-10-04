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
		Schema::create('atributo_valores', function (Blueprint $table) {
			$table->id();
			$table->foreignId('atributo_id')->constrained('atributos')->cascadeOnDelete();
			$table->string('valor'); // Ej: Rojo, Azul, S, M
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('atributo_valores');
	}
};
