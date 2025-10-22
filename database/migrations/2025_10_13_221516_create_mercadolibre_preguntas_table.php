<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::create('mercadolibre_preguntas', function (Blueprint $table) {
			$table->id();
			$table->string('mercadolibre_pregunta_id')->unique();
			$table->string('item_id');
			$table->unsignedBigInteger('seller_id');
			$table->unsignedBigInteger('from_user_id')->nullable();
			$table->text('text');
			$table->string('status')->nullable();
			$table->timestamp('date_created')->nullable();
			$table->json('payload')->nullable(); // Guardamos el JSON completo de la pregunta
			$table->timestamps();
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('mercadolibre_preguntas');
	}
};
