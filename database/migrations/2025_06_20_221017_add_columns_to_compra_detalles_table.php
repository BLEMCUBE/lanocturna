<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToCompraDetallesTable extends Migration
{


	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('compra_detalles', function (Blueprint $table) {
		 	$table->float('precio',8,2)->default(0);
            $table->float('precio_sin_iva',8,2)->default(0);
            $table->float('total',8,2)->default(0);
            $table->float('total_sin_iva',8,2)->default(0);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('compra_detalles', function (Blueprint $table) {
			$table->dropColumn('precio');
			$table->dropColumn('precio_sin_iva');
			$table->dropColumn('total');
			$table->dropColumn('total_sin_iva');
		});
	}
};
