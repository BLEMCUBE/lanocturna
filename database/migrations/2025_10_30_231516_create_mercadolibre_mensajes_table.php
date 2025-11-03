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
		Schema::create('mercadolibre_mensajes', function (Blueprint $table) {
			$table->id();
			$table->string('mercadolibre_venta_id')->nullable();
			$table->string('message_id')->unique();
			$table->string('sender_id')->nullable();
			$table->string('receiver_id')->nullable();
			$table->text('body');
			$table->string('status')->default('available');
			$table->boolean('read')->default(false);
			$table->json('payload')->nullable();
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('mercadolibre_mensajes');
	}
};
