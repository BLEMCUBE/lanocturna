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
		Schema::create('ml_mensajes', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('client_id')->nullable();
			$table->string('message_id');
			$table->string('conversation_id')->nullable();
			$table->unsignedBigInteger('order_id')->nullable();
			$table->unsignedBigInteger('pack_id')->nullable();
			$table->text('text')->nullable();
			$table->string('from_user_id')->nullable();
			$table->string('to_user_id')->nullable();
			$table->boolean('is_from_seller')->default(0);
			$table->string('attachment_path')->nullable();
			$table->timestamp('date_created')->nullable();
			$table->timestamp('received_at')->nullable();
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
		Schema::dropIfExists('ml_mensajes');
	}
};
