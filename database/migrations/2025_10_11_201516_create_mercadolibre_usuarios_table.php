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
		Schema::create('mercadolibre_usuarios', function (Blueprint $table) {
			$table->id();
			$table->string('meli_user_id')->nullable();
			$table->string('nickname');
			$table->string('email')->nullable();
			$table->text('access_token')->nullable();
			$table->text('refresh_token')->nullable();
			$table->timestamp('expires_at')->nullable();
			$table->foreignId('cliente_id')->constrained('mercadolibre_clientes')->onDelete('cascade');
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('mercadolibre_usuarios');
	}
};
