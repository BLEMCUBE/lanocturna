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
        Schema::create('productos_yuanes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('producto_id')->nullable();
            $table->unsignedBigInteger('tipo_cambio_yuan_id')->nullable();
            $table->unsignedBigInteger('importacion_id')->nullable();
            $table->timestamps();

            $table->foreign('producto_id')
            ->references('id')
            ->on('productos')
            ->onDelete('cascade');

            $table->foreign('tipo_cambio_yuan_id')
            ->references('id')
            ->on('tipo_cambio_yuanes')
            ->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos_yuanes');
    }
};
