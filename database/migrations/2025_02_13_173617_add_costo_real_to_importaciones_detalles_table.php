<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCostoRealToImportacionesDetallesTable extends Migration
{


    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('importaciones_detalles', function (Blueprint $table) {
			$table->float('costo_real',8, 2)->default(0)
                ->after('precio')->comment('costo real producto')
                ->default(0);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('importaciones_detalles', function (Blueprint $table) {
            $table->dropColumn('costo_real');
        });
    }
};
