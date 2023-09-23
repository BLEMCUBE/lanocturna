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
        Schema::create('depositos_productos', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->nullable();
            $table->string('unidad')->nullable();
            $table->integer('pcs_bulto')->default(0);
            $table->integer('bultos')->default(0);
            $table->float('cantidad_total',8,2)->default(0);
            $table->string('codigo_barra')->nullable();
            $table->unsignedBigInteger('deposito_lista_id')->nullable();
            $table->timestamps();

            $table->foreign('deposito_lista_id')
            ->references('id')
            ->on('depositos_listas')
            ->onDelete('set null')
            ->onUpdate('set null');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('depositos_productos');
    }
};
