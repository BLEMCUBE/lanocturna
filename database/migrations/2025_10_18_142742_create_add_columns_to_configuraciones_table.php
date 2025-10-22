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
		Schema::table('configuraciones', function (Blueprint $table) {
			$table->string('type')->default('text'); // text|textarea|number|select|checkbox|radio|date|switch|json
			$table->json('options')->nullable(); // para select/radio/checkbox [{label,value}]

		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('configuraciones', function (Blueprint $table) {
			$table->dropColumn(['type', 'options']);
		});
	}
};
