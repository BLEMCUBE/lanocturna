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
        Schema::create('importaciones_detalles', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->nullable();
            $table->float('precio',8,2)->default(0);
            $table->string('unidad')->nullable();
            $table->integer('pcs_bulto')->default(0);
            $table->integer('bultos')->nullable();
            $table->float('cantidad_total',8,2)->default(0);
            $table->float('valor_total',8,2)->default(0);
            $table->float('cbm_bulto',8,2)->default(0);
            $table->float('cbm_total',8,2)->default(0);
            $table->string('codigo_barra')->nullable();
            $table->string('estado')->nullable();
            $table->string('mueve_stock')->nullable();
            $table->unsignedBigInteger('importacion_id')->nullable();

            $table->foreign('importacion_id')
            ->references('id')
            ->on('importaciones')
            ->onDelete('set null')
            ->onUpdate('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('importaciones_detalles');
    }
};
