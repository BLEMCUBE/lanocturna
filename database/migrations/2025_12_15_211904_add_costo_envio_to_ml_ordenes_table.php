<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::table('ml_ordenes', function (Blueprint $table) {

			$table->json('costo_envio')->nullable()->after('payload');
		});
	}


	public function down(): void
	{
		Schema::table('ml_ordenes', function (Blueprint $table) {
			$table->dropColumn([
				'costo_envio'
			]);
		});
	}
};
