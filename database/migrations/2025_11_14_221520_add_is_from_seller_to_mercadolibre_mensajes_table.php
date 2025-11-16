<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	/**
	 * Run the migrations.
	 */
	public function up()
	{
		Schema::table('mercadolibre_mensajes', function (Blueprint $table) {
			$table->boolean('is_from_seller')->default(0)->after('to_user_id');
		});
	}

	public function down()
	{
		Schema::table('mercadolibre_mensajes', function (Blueprint $table) {
			$table->dropColumn('is_from_seller');
		});
	}
};
