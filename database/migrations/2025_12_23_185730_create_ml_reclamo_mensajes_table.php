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
		Schema::create('ml_reclamo_mensajes', function (Blueprint $table) {

			$table->id();
			$table->unsignedBigInteger('reclamo_id');
			$table->string('hash')->unique(); // 👈 ID REAL DEL MENSAJE
			$table->enum('sender_role', ['complainant', 'respondent', 'mediator']);
			$table->enum('receiver_role', ['complainant', 'respondent', 'mediator'])->nullable();
			$table->text('text')->nullable();
			$table->string('attachment_path')->nullable();
			$table->timestamp('date_created');
			$table->timestamp('date_read')->nullable();
			$table->boolean('translated')->default(false);
			$table->json('payload')->nullable();
			$table->timestamps();
			$table->index('reclamo_id');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('ml_reclamo_mensajes');
	}
};
