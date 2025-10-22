<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::create('mercadolibre_respuestas', function (Blueprint $table) {
			$table->id();
			$table->foreignId('mercadolibre_pregunta_id')->constrained('mercadolibre_preguntas')->onDelete('cascade');
			$table->unsignedBigInteger('from_user_id')->nullable();
			$table->text('text');
			$table->timestamp('date_created')->nullable();
			$table->json('payload')->nullable(); // Guardamos el JSON completo de la respuesta
			$table->timestamps();
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('mercadolibre_respuestas');
	}
};
