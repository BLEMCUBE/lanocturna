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
		Schema::create('ml_reclamos', function (Blueprint $table) {
			$table->id();
			// No foreign key — se queda aunque el cliente se elimine
			$table->bigInteger('meli_user_id')->index();
			$table->string('reclamo_id')->unique();
			$table->string('order_id')->nullable();
			$table->string('resource')->nullable();
			$table->string('status')->nullable();
			$table->string('reason')->nullable();
			$table->string('substatus')->nullable();
			$table->json('payload')->nullable();

			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('ml_reclamos');
	}
};
