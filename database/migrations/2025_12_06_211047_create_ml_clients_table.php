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

		Schema::create('ml_clients', function (Blueprint $table) {
			$table->id();
			$table->bigInteger('meli_user_id')->nullable();
			$table->string('nickname');
			$table->string('access_token')->nullable();
			$table->string('refresh_token')->nullable();
			$table->string('email')->nullable();
			$table->timestamp('expires_at')->nullable();
			$table->json('payload')->nullable();
			$table->foreignId('app_id')->constrained('ml_apps');
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('ml_clients');
	}
};
