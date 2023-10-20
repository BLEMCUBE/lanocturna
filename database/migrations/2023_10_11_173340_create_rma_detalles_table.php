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
        Schema::create('rma_detalles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rma_id')->nullable();
            $table->unsignedBigInteger('producto_id')->nullable();
            $table->float('cantidad',8,2)->default(0);
            $table->float('precio',8,2)->default(0);
            $table->float('total',8,2)->default(0);
            $table->string('nro_serie')->nullable();
            $table->string('defecto')->nullable();
            $table->timestamps();

            $table->foreign('rma_id')
            ->references('id')
            ->on('rmas')
            ->onDelete('cascade');

            $table->foreign('producto_id')
            ->references('id')
            ->on('productos')
            ->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rma_detalles');
    }
};
