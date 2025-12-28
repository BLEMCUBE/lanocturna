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
			$table->string('client_id')->nullable()->after('is_from_seller');
		});
	}

	public function down()
	{
		Schema::table('mercadolibre_mensajes', function (Blueprint $table) {
			$table->dropColumn('client_id');
		});
	}
};
