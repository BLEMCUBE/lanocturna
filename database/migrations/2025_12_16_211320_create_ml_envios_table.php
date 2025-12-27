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
		Schema::create('ml_envios', function (Blueprint $table) {
			$table->id();
			$table->string('envio_id')->unique()->index(); // ID de ML
			$table->unsignedBigInteger('orden_id')->index()->nullable();             // ID de la orden asociada
			$table->string('estado')->nullable();           // estado actual
			$table->string('modo_envio')->nullable();    // courier, mode
			$table->string('nro_rastreo')->nullable();  // número de rastreo
			$table->timestamp('fecha_envio')->nullable();  // fecha de envío
			$table->timestamp('fecha_entrega')->nullable(); // fecha de entrega (si aplica)
			$table->json('costo')->nullable();
			$table->json('payload')->nullable();            // objeto completo de ML

			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('ml_envios');
	}
};
