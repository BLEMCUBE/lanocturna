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
        Schema::create('compras', function (Blueprint $table) {
            $table->id();
            $table->string('nro_factura')->nullable();
            $table->string('proveedor')->nullable();
            $table->string('estado')->default('COMPLETADO');
            $table->timestamp('fecha_anulacion')->nullable();
            $table->text('observaciones')->nullable();
            $table->unsignedBigInteger('facturador_id')->nullable();
            $table->timestamps();

            $table->foreign('facturador_id')
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
        Schema::dropIfExists('compras');
    }
};
