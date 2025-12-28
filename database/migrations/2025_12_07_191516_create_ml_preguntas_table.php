<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::create('ml_preguntas', function (Blueprint $table) {
			$table->id();
			$table->string('pregunta_id')->unique();
			$table->string('item_id');
			$table->unsignedBigInteger('seller_id');
			$table->unsignedBigInteger('from_user_id')->nullable();
			$table->text('text');
			$table->string('status')->nullable();
			$table->boolean('is_read')->default(0);
			$table->timestamp('date_created')->nullable();
			$table->json('payload')->nullable();
			$table->timestamps();
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('ml_preguntas');
	}
};
