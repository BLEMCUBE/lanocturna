<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToComprasTable extends Migration
{


	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('compras', function (Blueprint $table) {
			$table->string('moneda')->nullable();
			$table->float('tipo_cambio', 8, 2)->default(0);
			$table->float('total', 8, 2)->default(0);
			$table->float('total_sin_iva', 8, 2)->default(0);
			$table->unsignedBigInteger('comprador_id')->nullable();

			$table->foreign('comprador_id')
				->references('id')
				->on('users')
				->onDelete('set null')
				->onUpdate('set null');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('compras', function (Blueprint $table) {
			$table->dropColumn('moneda');
			$table->dropColumn('tipo_cambio');
			$table->dropColumn('total');
			$table->dropColumn('total_sin_iva');
			$table->dropColumn('comprador_id');
		});
	}
};
