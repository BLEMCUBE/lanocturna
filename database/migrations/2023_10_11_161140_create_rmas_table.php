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
        Schema::create('rmas', function (Blueprint $table) {
            $table->id();
            $table->string('nro_servicio')->nullable();
            $table->date('fecha_ingreso')->nullable();
            $table->date('fecha_compra')->nullable();
            $table->string('nro_factura')->nullable();
            $table->json('cliente')->nullable();
            $table->string('estado')->default('INGRESADO');
            $table->string('tipo')->nullable();
            $table->unsignedBigInteger('producto_id')->nullable();
            $table->float('prod_cantidad',8,2)->default(1);
            $table->string('prod_origen')->nullable();
            $table->string('prod_nombre')->nullable();
            $table->string('prod_serie')->nullable();
            $table->float('costo_presupuestado',8,2)->default(0);
            $table->text('observaciones')->nullable();
            $table->text('defecto')->nullable();
            $table->unsignedBigInteger('vendedor_id')->nullable();
            $table->timestamps();

            $table->foreign('vendedor_id')
            ->references('id')
            ->on('users')
            ->onDelete('set null')
            ->onUpdate('set null');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rmas');
    }
};
