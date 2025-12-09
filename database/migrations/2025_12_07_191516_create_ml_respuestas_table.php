<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::create('ml_respuestas', function (Blueprint $table) {
			$table->id();
			$table->string('respuesta_id')->unique()->nullable();
			$table->string('pregunta_id');
			$table->unsignedBigInteger('from_user_id')->nullable();
			$table->text('text');
			$table->timestamp('date_created')->nullable();
			$table->json('payload')->nullable(); // Guardamos el JSON completo de la respuesta
            $table->foreign('pregunta_id')->references('pregunta_id')->on('ml_preguntas')->onDelete('cascade');
			$table->timestamps();

		});
	}

	public function down(): void
	{
		Schema::dropIfExists('ml_respuestas');
	}
};
