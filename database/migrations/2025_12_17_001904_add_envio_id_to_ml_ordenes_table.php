<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::table('ml_ordenes', function (Blueprint $table) {

			$table->unsignedBigInteger('envio_id')->nullable()->after('payload')->index();
		});
	}

	public function down(): void
	{
		Schema::table('ml_ordenes', function (Blueprint $table) {
			$table->dropColumn([
				'envio_id'
			]);
		});
	}
};
