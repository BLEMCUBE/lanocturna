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
			$table->string('pack_id')->index()->nullable();
			$table->string('pack_status')->nullable();
			$table->text('claim_id')->nullable();
			$table->string('claim_status')->nullable();
			$table->timestamp('date_created')->nullable();
			$table->string('message_id')->unique();
			$table->string('from_user_id')->nullable();
			$table->string('to_user_id')->nullable();
			$table->text('body')->nullable();
			$table->string('attachment_path')->nullable();
			$table->boolean('is_read')->default(false);
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
