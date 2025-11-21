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

		Schema::create('mercadolibre_clientes', function (Blueprint $table) {
			$table->id();
			$table->string('nombre');
			$table->string('client_id');
			$table->string('client_secret');
		    $table->integer('is_default')->default(0);
			$table->string('redirect_uri');

			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('mercadolibre_clientes');
	}
};
