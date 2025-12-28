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
		Schema::create('ml_notificaciones', function (Blueprint $table) {
			$table->id();
			$table->string('topic')->nullable();
			$table->string('actions')->nullable();
			$table->string('resource')->nullable();
			$table->bigInteger('user_id')->nullable();
			$table->bigInteger('application_id')->nullable();
			$table->timestamp('sent_at')->nullable();
			$table->integer('attempts')->default(1);
			$table->string('status')->default('received'); // received, processed, failed
			$table->json('payload')->nullable(); // guarda la notificación completa
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('ml_notificaciones');
	}
};
