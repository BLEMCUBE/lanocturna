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
		Schema::table('productos', function (Blueprint $table) {
			$table->boolean('is_store')->default(0)->after('en_camino');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('productos', function (Blueprint $table) {
			$table->dropColumn('is_store');
		});
	}
};
