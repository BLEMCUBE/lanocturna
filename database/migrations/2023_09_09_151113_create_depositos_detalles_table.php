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
        Schema::create('depositos_detalles', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->nullable();
            $table->string('unidad')->nullable();
            $table->integer('pcs_bulto')->default(0);
            $table->integer('bultos')->nullable();
            $table->float('cantidad_total',8,2)->default(0);
            $table->string('codigo_barra')->nullable();
            $table->unsignedBigInteger('deposito_id')->nullable();
            $table->unsignedBigInteger('importacion_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();

            $table->foreign('deposito_id')
            ->references('id')
            ->on('depositos')
            ->onDelete('set null')
            ->onUpdate('set null');

            $table->foreign('importacion_id')
            ->references('id')
            ->on('importaciones')
            ->onDelete('set null')
            ->onUpdate('set null');

            $table->foreign('user_id')
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
        Schema::dropIfExists('depositos_detalles');
    }
};
