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
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->nullable();
            $table->float('porcentaje_impuesto',8,2)->nullable();
            $table->float('impuesto',8,2)->default(0);
            $table->float('neto',8,2)->default(0);
            $table->float('total',8,2)->default(0);
            $table->string('destino')->nullable();
            $table->boolean('facturado')->default(false);
            $table->boolean('validado')->default(false);
            $table->string('estado')->default('PENDIENTE A FACTURACIÓN');
            $table->text('observaciones')->nullable();
            $table->json('cliente')->nullable();
            $table->unsignedBigInteger('vendedor_id')->nullable();
            $table->unsignedBigInteger('facturador_id')->nullable();
            $table->timestamps();

            $table->foreign('vendedor_id')
            ->references('id')
            ->on('users')
            ->onDelete('set null')
            ->onUpdate('set null');

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
        Schema::dropIfExists('ventas');
    }
};
