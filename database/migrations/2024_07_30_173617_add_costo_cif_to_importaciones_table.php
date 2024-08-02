<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCostoCifToImportacionesTable extends Migration
{


    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('importaciones', function (Blueprint $table) {
            $table->float('costo_cif',8,2)->default(0)
                ->after('total')->comment('costo cif importacion')
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
        Schema::table('importaciones', function (Blueprint $table) {
            $table->dropColumn('costo_cif');
        });
    }
};
