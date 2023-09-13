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
        Schema::create('depositos_general', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->nullable();
            $table->string('unidad')->nullable();
            $table->integer('pcs_bulto')->default(0);
            $table->integer('bultos')->nullable();
            $table->float('cantidad_total',8,2)->default(0);
            $table->string('codigo_barra')->nullable();
            $table->timestamps();


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('depositos_general');
    }
};
