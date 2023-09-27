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
        Schema::create('depositos', function (Blueprint $table) {
            $table->id();
            $table->string('nro_carpeta')->nullable();
            $table->string('nro_contenedor')->nullable();
            $table->string('estado')->nullable();
            $table->float('total',8,2)->default(0);
            $table->timestamp('fecha_arribado')->nullable();
            $table->timestamp('fecha_camino')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
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
        Schema::dropIfExists('depositos');
    }
};
